<?php

namespace SayHello\GitInstaller;

class Settings
{

    public static string $key = 'shgi-settings';
    public array $registered_settings = [];

    public function __construct()
    {
    }

    public function run()
    {
        add_action('rest_api_init', [$this, 'registerRoute']);
    }

    public function getRegisteredSettings()
    {
        return apply_filters('shgi/Settings/register', $this->registered_settings);
    }

    public function registerRoute()
    {
        register_rest_route(sayhelloGitInstaller()->api_namespace, 'settings', [
            'methods' => 'POST',
            'callback' => [$this, 'apiUpdateSetting'],
            'permission_callback' => function () {
                return current_user_can(Helpers::$authAdmin);
            }
        ]);

        register_rest_route(sayhelloGitInstaller()->api_namespace, 'settings', [
            'methods' => 'GET',
            'callback' => [$this, 'apiGetSettings'],
            'permission_callback' => function () {
                return current_user_can(Helpers::$authAdmin);
            }
        ]);
    }

    public function apiUpdateSetting($req)
    {
        $params = [];
        foreach ($req->get_params() as $key => $value) {
            if (array_key_exists($key, $this->getRegisteredSettings())) {
                $params[$key] = $value;
            }
        }

        return $this->updateSettings($params);
    }

    public function apiGetSettings(): array
    {
        $return = [];
        $valid_settings_keys = array_keys($this->getRegisteredSettings());
        foreach ($valid_settings_keys as $key) {
            $return[$key] = $this->getSingleSetting($key, $this->getSettings());
        }

        return $return;
    }

    public function regsterSettings($key, $default_value, $validation)
    {
        $this->getRegisteredSettings()[$key] = [
            'default' => $default_value,
            'validate' => $validation ? function ($value) use ($validation) {
                return $validation($value);
            } : null,
        ];
    }

    public function getSettings($keys_to_return = []): array
    {
        $saved_options = get_option(self::$key, []);
        $registered_settings = $this->getRegisteredSettings();

        if (count($keys_to_return) === 0) {
            $keys_to_return = array_keys($registered_settings);
        }

        $settings_to_return = [];
        foreach ($keys_to_return as $settings_key) {
            $settings_to_return[$settings_key] = [
                'value' => array_key_exists(
                    $settings_key,
                    $saved_options
                ) ? $saved_options[$settings_key] : $registered_settings[$settings_key]['default'],
                'label' => array_key_exists(
                    'label',
                    $registered_settings[$settings_key]
                ) ? $registered_settings[$settings_key]['label'] : '',
                'values' => array_key_exists(
                    'values',
                    $registered_settings[$settings_key]
                ) ? $registered_settings[$settings_key]['values'] : null,
            ];
        }

        return $settings_to_return;
    }

    public function getSingleSetting($key, $all_settings = null)
    {
        if (!array_key_exists($key, $this->getRegisteredSettings())) {
            return null;
        }

        if (null === $all_settings) {
            $all_settings = $this->getSettings([$key]);
        }

        return $all_settings[$key];
    }

    public function getSingleSettingValue($key)
    {
        $setting = $this->getSingleSetting($key);
        if (!$setting) {
            return null;
        }

        return $setting['value'];
    }

    public function updateSettings($settings)
    {
        $errors = [];

        foreach ($settings as $key => $value) {
            $validate = $this->validateSetting($key, $value);
            if (is_wp_error($validate)) {
                $errors[$key] = $validate->get_error_message();
            }
        }

        if (count($errors) !== 0) {
            $message = '';
            $message .= '<ul>';
            foreach ($errors as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= '</ul>';

            return new \WP_Error(
                'validation_failed',
                $message,
                [
                    'status' => 400,
                    'data' => $errors,
                ]
            );
        }

        $options = $this->getSettings();
        foreach ($options as $key => $setting) {
            $options[$key] = $setting['value'];
        }

        $oldValues = $this->getSettings();
        update_option(self::$key, array_merge($options, $settings));
        return $this->getSettings();
    }

    public function validateSetting($key, $value)
    {
        $registered_settings = $this->getRegisteredSettings();
        if (!array_key_exists($key, $registered_settings)) {
            return new \WP_Error(
                'invalid_setting',
                sprintf(__('Invalid Settings key "%s"', 'shgi'), $key)
            );
        }

        $validate = $registered_settings[$key]['validate'] ?
            $registered_settings[$key]['validate']($value) :
            '';

        if ('' !== $validate) {
            return new \WP_Error(
                'invalid_setting_value',
                sprintf(__('%s: %s', 'shgi'), $key, $validate)
            );
        }

        return true;
    }
}

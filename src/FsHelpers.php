<?php

namespace SayHello\GitInstaller;

class FsHelpers
{
    public static function moveDir($from, $to): bool
    {
        return rename(
            $from,
            $to
        );
    }

    public static function removeDir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        self::removeDir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function unzip($zipFile, $dest)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($zipFile);
        if ($res !== true) {
            return new \WP_Error(
                'shgi_repo_unzip_failed',
                sprintf(
                    __('%s could not be unpacked', 'shgi'),
                    $zip
                )
            );
        }
        $zip->extractTo($dest);
        $zip->close();
        unlink($zipFile);
        return true;
    }

    public static function isInMaintenanceMode()
    {
        $file = ABSPATH . '.maintenance';
        return file_exists($file);
    }

    public static function maintenanceMode($enable = false)
    {
        $file = ABSPATH . '.maintenance';
        if ($enable) {
            $maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
            unlink($file);
            file_put_contents($file, $maintenance_string);
        } elseif (!$enable && file_exists($file)) {
            unlink($file);
        }
    }
}

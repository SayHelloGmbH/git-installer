import React from 'react';
import ReactDOM from 'react-dom';
import './App.css';
import { ToastProvider } from './components/toast/toastContext';
import PageGitPackages from './pages/PageGitPackages';
import { SettingsProvider } from './settings';
import { Page, TabNavigation } from './theme';
import { VARS } from './utils/constants';
import { pluginString } from './utils/pluginStrings';
import { Route, RouterProvider } from './utils/router';

const app = document.querySelector('#shgu-app');
const shadowbox = document.querySelector('#shgu-shadowbox');
if (!shadowbox) {
  const elem = document.createElement('div');
  elem.id = 'shgu-shadowbox';
  document.body.appendChild(elem);
}

const App = () => (
  <Page title={pluginString('plugin.name')}>
    <TabNavigation />
    <Route page="git-packages">
      <PageGitPackages />
    </Route>
  </Page>
);

if (app) {
  ReactDOM.render(
    <ToastProvider>
      <SettingsProvider>
        <RouterProvider>
          <App />
        </RouterProvider>
      </SettingsProvider>
    </ToastProvider>,
    app
  );
}

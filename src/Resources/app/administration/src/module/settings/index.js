import './page/swag-language-pack-settings';
import './components/swag-language-pack-settings-icon';

import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

Module.register('swag-language-pack', {
    type: 'plugin',
    name: 'SwagLanguagePack',
    title: 'swag-language-pack.general.mainMenuItemGeneral',
    description: 'swag-language-pack.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#6ac30b',
    icon: 'default-action-settings',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        index: {
            component: 'swag-language-pack-settings',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'language.viewer'
            }
        }
    },

    settingsItem: {
        group: 'plugins',
        to: 'swag.language.pack.index',
        iconComponent: 'swag-language-pack-settings-icon',
        backgroundEnabled: true,
        privilege: 'language.viewer'
    }
});

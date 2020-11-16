import './components/swag-language-pack-flag';
import './components/swag-pack-language-entry';
import './components/swag-language-pack-settings-icon';
import './page/swag-language-pack-settings';

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

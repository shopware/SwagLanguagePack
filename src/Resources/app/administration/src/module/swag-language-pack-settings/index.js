import './acl';
import './component/swag-language-pack-flag';
import './component/swag-pack-language-entry';
import './component/swag-language-pack-settings-icon';

import './page/swag-language-pack-settings';
import './page/swag-language-pack-settings-base';
import './page/swag-language-pack-settings-administration';
import './page/swag-language-pack-settings-sales-channel';

const { Module } = Shopware;

Module.register('swag-language-pack', {
    type: 'plugin',
    name: 'SwagLanguagePack',
    title: 'swag-language-pack.general.mainMenuItemGeneral',
    description: 'swag-language-pack.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'regular-cog',

    routes: {
        index: {
            component: 'swag-language-pack-settings',
            path: 'index',
            redirect: {
                name: 'swag.language.pack.index.administration',
            },
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'language.viewer',
            },
            children: {
                administration: {
                    component: 'swag-language-pack-settings-administration',
                    path: 'administration',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'language.viewer',
                    },
                },
                salesChannel: {
                    component: 'swag-language-pack-settings-sales-channel',
                    path: 'sales-channel',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'language.viewer',
                    },
                },
            },
        },
    },

    settingsItem: {
        group: 'plugins',
        to: 'swag.language.pack.index',
        icon: 'regular-language',
        backgroundEnabled: true,
        privilege: 'language.viewer',
    },
});

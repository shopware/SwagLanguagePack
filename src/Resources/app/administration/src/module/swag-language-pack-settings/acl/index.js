Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'settings',
    key: 'language',
    roles: {
        viewer: {
            privileges: [
                'sales_channel:read',
                'sales_channel_domain:read',
            ],
        },
        editor: {
            privileges: [
                'swag_language_pack_language:update',
                'user:read',
                'user:update',
            ],
        },
    },
});

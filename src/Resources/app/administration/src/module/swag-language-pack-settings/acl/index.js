Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'settings',
    key: 'language',
    roles: {
        editor: {
            privileges: [
                'swag_language_pack_language:update'
            ]
        }
    }
});

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * @deprecated tag:v3.1.0 - Will be removed. USe `sw-sales-channel-defaults-select` instead
 */
Component.extend('swag-sales-channel-defaults-select-filterable', 'sw-sales-channel-defaults-select', {
    props: {
        criteria: {
            type: Object,
            required: false,
            default() {
                return new Criteria(1, 25);
            },
        },
    },
});

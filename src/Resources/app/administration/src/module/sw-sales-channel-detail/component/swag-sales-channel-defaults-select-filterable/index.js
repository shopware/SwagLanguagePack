import template from './swag-sales-channel-defaults-select-filterable.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.extend('swag-sales-channel-defaults-select-filterable', 'sw-sales-channel-defaults-select', {
    template,

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

import template from './swag-language-pack-settings-storefront.html.twig';

const { Component } = Shopware;

Component.register('swag-language-pack-settings-storefront', {
    template,

    props: {
        isLoading: {
            type: Boolean,
            required: true
        },

        packLanguages: {
            type: Array,
            required: true
        }
    }
});

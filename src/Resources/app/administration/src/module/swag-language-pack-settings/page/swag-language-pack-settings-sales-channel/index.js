import template from './swag-language-pack-settings-sales-channel.html.twig';

const { Component } = Shopware;

Component.register('swag-language-pack-settings-sales-channel', {
    template,

    props: {
        isLoading: {
            type: Boolean,
            required: true,
        },

        packLanguages: {
            type: Array,
            required: true,
        },
    },
});

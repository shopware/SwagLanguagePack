import template from './swag-language-pack-settings-administration.html.twig';

const { Component } = Shopware;

Component.register('swag-language-pack-settings-administration', {
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

import template from './swag-pack-language-entry.html.twig';
import './swag-pack-language-entry.scss';

const { Component } = Shopware;

Component.register('swag-pack-language-entry', {
    template,

    props: {
        value: {
            type: Object,
            required: true
        },

        field: {
            type: String,
            required: true
        },

        label: {
            type: String,
            required: true
        },

        description: {
            type: String,
            required: false,
            default: ''
        },

        flagLocale: {
            type: String,
            required: false,
            default: ''
        },

        disabled: {
            type: Boolean,
            required: false,
            default: false
        }
    }
});

import template from './swag-language-pack-flag.html.twig';
import './swag-language-pack-flag.scss';

const { Component } = Shopware;

/**
 * @deprecated tag:v6.0.0 - Will be removed. Use native `img` tag instead
 */
Component.register('swag-language-pack-flag', {
    template,

    props: {
        locale: {
            type: String,
            required: false,
            default: '',
        },
    },

    computed: {
        assetFilter() {
            return Shopware.Filter.getByName('asset');
        },
        countryCode() {
            return this.locale.split('-')[1].toLowerCase();
        },
    },
});

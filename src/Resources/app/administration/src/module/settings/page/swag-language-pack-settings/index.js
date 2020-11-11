import template from './index.html.twig';

const { Component } = Shopware;

Component.register('swag-language-pack-settings', {
    template,

    data() {
        return {
            isLoading: false
        };
    }
});

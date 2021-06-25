import template from './swag-language-pack-settings-base.html.twig';
import './swag-language-pack-settings-base.scss';

const { Component } = Shopware;

Component.register('swag-language-pack-settings-base', {
    template,

    inject: [
        'acl',
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: true,
        },

        packLanguages: {
            type: Array,
            required: true,
        },

        settingsType: {
            type: String,
            required: true,
            validator(value) {
                return ['administration', 'salesChannel'].includes(value);
            },
        },
    },

    computed: {
        description() {
            const userInterfaceLanguageLink = `<a href="#/sw/profile/index">
                ${this.$tc('swag-language-pack.settings.card.administration.descriptionTargetLinkText')}
            </a>`;

            return this.$tc(`swag-language-pack.settings.card.${this.settingsType}.description`, 0, {
                userInterfaceLanguageLink,
            });
        },
    },
});

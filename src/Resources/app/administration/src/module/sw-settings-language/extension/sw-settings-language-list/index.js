import template from './sw-settings-language-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-settings-language-list', {
    template,

    data() {
        return {
            packLanguageLanguageIds: [],
        };
    },

    computed: {
        packLanguageCriteria() {
            return (new Criteria(1, 50)).addFilter(
                Criteria.not('and', [
                    Criteria.equals('swagLanguagePackLanguage.id', null),
                ]),
            );
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getPackLanguageLanguageIds();
        },

        getPackLanguageLanguageIds() {
            this.languageRepository.searchIds(this.packLanguageCriteria).then((result) => {
                this.packLanguageLanguageIds = result.data;
            });
        },

        isPackLanguage(languageId) {
            return this.packLanguageLanguageIds.includes(languageId);
        },
    },
});

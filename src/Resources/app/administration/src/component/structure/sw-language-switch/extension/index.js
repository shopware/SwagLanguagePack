const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-language-switch', {
    computed: {
        languageCriteria() {
            return this.$super('languageCriteria').addFilter(
                Criteria.multi('OR', [
                    Criteria.equals('extensions.swagLanguagePackLanguage.id', null),
                    Criteria.equals('extensions.swagLanguagePackLanguage.salesChannelActive', true),
                    Criteria.equals('id', Defaults.systemLanguageId),
                ]),
            );
        },
    },
});

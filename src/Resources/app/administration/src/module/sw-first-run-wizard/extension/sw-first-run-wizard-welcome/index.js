const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-first-run-wizard-welcome', {
    methods: {
        getLanguageCriteria() {
            return this.$super('getLanguageCriteria')
                .addAssociation('swagLanguagePackLanguage')
                .addSorting(Criteria.sort('name', 'ASC'))
                .addFilter(Criteria.multi('OR', [
                    Criteria.equals('extensions.swagLanguagePackLanguage.id', null),
                    Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', true),
                    Criteria.equals('id', Defaults.systemLanguageId),
                ]))
                .setLimit(null);
        },
    },
});

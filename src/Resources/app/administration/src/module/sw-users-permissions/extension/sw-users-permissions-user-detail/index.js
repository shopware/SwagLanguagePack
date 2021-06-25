const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-users-permissions-user-detail', {
    computed: {
        languageCriteria() {
            const criteria = this.$super('languageCriteria');
            criteria.addFilter(Criteria.multi('OR', [
                Criteria.equals('extensions.swagLanguagePackLanguage.id', null),
                Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', true),
                Criteria.equals('id', Shopware.Defaults.systemLanguageId),
            ]));

            return criteria;
        },
    },
});

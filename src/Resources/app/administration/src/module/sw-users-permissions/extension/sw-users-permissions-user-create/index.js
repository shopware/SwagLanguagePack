const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-users-permissions-user-create', {
    computed: {
        languageCriteria() {
            return this.$super('languageCriteria')
                .addFilter(Criteria.multi('OR', [
                    Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', true),
                    Criteria.equals('id', Shopware.Defaults.systemLanguageId),
                    Criteria.equalsAny('name', ['English', 'Deutsch'])
                ]));
        }
    }
});

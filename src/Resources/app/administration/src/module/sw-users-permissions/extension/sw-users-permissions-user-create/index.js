const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-users-permissions-user-create', {
    computed: {
        languageCriteria() {
            return this.$super('languageCriteria')
                .addFilter(Criteria.multi('OR', [
                    Criteria.equals('extensions.swagLanguagePackLanguage.id', null),
                    Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', true),
                    Criteria.equals('id', Shopware.Defaults.systemLanguageId),
                ]));
        },
    },

    methods: {
        onSave() {
            // This override is needed to fix the broken inheritance
            this.$super('onSave');
        },
    },
});

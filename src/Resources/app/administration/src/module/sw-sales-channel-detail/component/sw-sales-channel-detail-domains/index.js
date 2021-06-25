const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-domains', {
    computed: {
        snippetSetCriteria() {
            const locales = this.salesChannel.languages.map(language => language.locale.code);

            return this.$super('snippetSetCriteria')
                .addFilter(Criteria.equalsAny('iso', locales));
        },
    },
});

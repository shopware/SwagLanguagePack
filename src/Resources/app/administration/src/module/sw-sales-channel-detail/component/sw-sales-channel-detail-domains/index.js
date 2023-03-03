const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-domains', {
    computed: {
        snippetSetCriteria() {
            const locales = this.salesChannel.languages.map(language => language.locale?.code).filter(Boolean);

            if (locales.length === 0) {
                return this.$super('snippetSetCriteria');
            }

            return this.$super('snippetSetCriteria')
                .addFilter(Criteria.equalsAny('iso', locales));
        },
    },
});

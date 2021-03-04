import template from './sw-sales-channel-detail-domains.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-domains', {
    template,

    computed: {
        snippetSetCriteria() {
            const locales = this.salesChannel.languages.map(language => language.locale.code);

            // ToDo LAN-52 - Use `this.$super('snippetSetCriteria')` in 6.4.0.0 to extend the criteria
            return (new Criteria())
                .addFilter(Criteria.equalsAny('iso', locales));
        }
    }
});

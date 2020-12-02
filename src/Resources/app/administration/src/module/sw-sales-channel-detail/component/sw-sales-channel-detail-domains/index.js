import template from './sw-sales-channel-detail-domains.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-domains', {
    template,

    computed: {
        snippetSetCriteria() {
            const locales = this.salesChannel.languages.map(language => language.locale.code);

            return (new Criteria())
                .addFilter(Criteria.equalsAny('iso', locales));
        }
    }
});

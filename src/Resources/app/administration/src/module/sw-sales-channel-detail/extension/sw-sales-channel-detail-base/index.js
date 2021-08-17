import template from './sw-sales-channel-detail-base.html.twig';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.override('sw-sales-channel-detail-base', {
    template,

    computed: {
        languageCriteria() {
            return (new Criteria())
                .addAssociation('swagLanguagePackLanguage')
                .addAssociation('locale')
                .addSorting(Criteria.sort('name', 'ASC'))
                .addFilter(Criteria.multi('OR', [
                    Criteria.equals('extensions.swagLanguagePackLanguage.id', null),
                    Criteria.equals('extensions.swagLanguagePackLanguage.salesChannelActive', true),
                    Criteria.equals('id', Defaults.systemLanguageId),
                ]));
        },
    },
});

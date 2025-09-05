Shopware.Component.override('sw-sales-channel-detail-base', {
    computed: {
        languageCriteria() {
            return this.$super('languageCriteria').addAssociation('locale');
        },
    },
});

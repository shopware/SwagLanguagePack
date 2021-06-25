const { Component } = Shopware;

Component.override('sw-sales-channel-detail', {
    methods: {
        getLoadSalesChannelCriteria() {
            return this.$super('getLoadSalesChannelCriteria')
                .addAssociation('languages.locale');
        },
    },
});

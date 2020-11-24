const { Component } = Shopware;

Component.override('sw-settings-language-list', {
    computed: {
        allowDelete() {
            return false;
        }
    }
});

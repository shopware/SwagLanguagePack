import template from './swag-language-pack-settings.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-language-pack-settings', {
    template,

    inject: [
        'repositoryFactory'
        // 'acl' // ToDo LAN-28 - Implement ACL
    ],

    mixin: [
        'notification'
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            packLanguages: []
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        packLanguageRepository() {
            return this.repositoryFactory.create('swag_language_pack_language');
        },

        packLanguageCriteria() {
            return new Criteria();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.loadPackLanguages();
        },

        loadPackLanguages() {
            this.isLoading = true;

            return this.packLanguageRepository.search(this.packLanguageCriteria, Shopware.Context.api).then((result) => {
                this.packLanguages = result;
            }).finally(() => {
                this.isLoading = false;
            });
        },

        onSave() {
            this.isLoading = true;

            return this.packLanguageRepository.saveAll(this.packLanguages, Shopware.Context.api).catch(() => {
                this.createNotificationError({
                    message: this.$tc('swag-language-pack.settings.card.messageSaveError')
                });
            }).finally(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;

                this.loadPackLanguages();
            });
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
        }
    }
});

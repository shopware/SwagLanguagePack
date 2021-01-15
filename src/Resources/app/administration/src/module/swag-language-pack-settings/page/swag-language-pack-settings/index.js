import template from './swag-language-pack-settings.html.twig';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-language-pack-settings', {
    template,

    inject: [
        'repositoryFactory',
        'userService',
        'acl'
    ],

    mixins: [
        'notification'
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            hasChanges: false,
            packLanguages: [],
            fallbackLocaleId: null,
            confirmPasswordModal: false
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

        languageRepository() {
            return this.repositoryFactory.create('language');
        },

        userRepository() {
            return this.repositoryFactory.create('user');
        },

        packLanguageCriteria() {
            return (new Criteria())
                .addSorting(Criteria.sort('language.name', 'ASC'))
                .addAssociation('language.salesChannels.domains');
        }
    },

    created() {
        this.createdComponent();
    },

    beforeRouteLeave(to, from, next) {
        next();

        if (this.hasChanges) {
            window.location.reload();
        }
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
            this.confirmPasswordModal = true;
        },

        onSaveFinish() {
            this.isSaveSuccessful = false;
        },

        onCloseConfirmPasswordModal() {
            this.confirmPasswordModal = false;
        },

        savePackLanguages() {
            this.isLoading = true;

            return this.packLanguageRepository.saveAll(this.packLanguages, Shopware.Context.api).then(() => {
                this.hasChanges = true;
                return this.resetInvalidUserLanguages();
            }).catch(() => {
                this.createNotificationError({
                    message: this.$tc('swag-language-pack.settings.card.messageSaveError')
                });
            }).finally(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;

                this.loadPackLanguages();
            });
        },

        async resetInvalidUserLanguages() {
            const currentUser = await this.userService.getUser();
            const invalidLocales = await this.fetchInvalidLocaleIds();
            const invalidUserCriteria = (new Criteria()).addFilter(
                Criteria.equalsAny('localeId', invalidLocales)
            );

            let invalidUsers = await this.userRepository.search(invalidUserCriteria, Shopware.Context.api);
            invalidUsers = invalidUsers.reduce((accumulator, user) => {
                user.localeId = this.fallbackLocaleId;

                // If we change the locale of the current logged in user
                if (currentUser.data.id === user.id) {
                    Shopware.Service('localeHelper').setLocaleWithId(user.localeId);
                }

                accumulator.push(user);

                return accumulator;
            }, []);

            return this.userRepository.saveAll(invalidUsers, Shopware.Context.api);
        },

        async fetchInvalidLocaleIds() {
            const invalidAdminLanguagesCriteria = (new Criteria())
                .addFilter(Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', false));
            const invalidAdminLanguages = await this.languageRepository.search(
                invalidAdminLanguagesCriteria,
                Shopware.Context.api
            );

            const fallbackLanguageCriteria = (new Criteria()).setIds([Defaults.systemLanguageId]);
            const fallbackLanguage = await this.languageRepository.search(fallbackLanguageCriteria, Shopware.Context.api);
            this.fallbackLocaleId = fallbackLanguage.first().localeId;

            return invalidAdminLanguages.map(language => language.localeId);
        }
    }
});

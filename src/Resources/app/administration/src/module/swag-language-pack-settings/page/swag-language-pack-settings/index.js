import template from './swag-language-pack-settings.html.twig';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-language-pack-settings', {
    template,

    inject: [
        'repositoryFactory',
        'userService',
        'acl',
    ],

    mixins: [
        'notification',
    ],

    data() {
        return {
            isLoading: false,
            isSaveSuccessful: false,
            hasChanges: false,
            packLanguages: [],
            fallbackLocaleId: null,
            confirmPasswordModal: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
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
                .addAssociation('language.salesChannels.domains')
                .addAssociation('language.locale');
        },
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

            return this.packLanguageRepository.search(this.packLanguageCriteria).then((result) => {
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

            return this.validateStates(this.packLanguages).then(() => {
                return this.packLanguageRepository.saveAll(this.packLanguages).then(() => {
                    this.hasChanges = true;
                    return this.resetInvalidUserLanguages();
                }).catch(() => {
                    this.createNotificationError({
                        message: this.$tc('swag-language-pack.settings.card.messageSaveError'),
                    });
                });
            }).catch((invalidLanguages) => {
                const languageList = invalidLanguages.map(packLanguage => packLanguage.language.name);
                const languages = `<b>${languageList.join(', ')}</b>`;

                this.createNotificationError({
                    message: this.$tc('swag-language-pack.settings.card.messageSalesChannelActiveError', 0, {
                        languages,
                    }),
                    autoClose: false,
                });
            }).finally(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;

                this.loadPackLanguages();
            });
        },

        validateStates(packLanguages) {
            return new Promise((resolve, reject) => {
                const invalidLanguages = packLanguages.filter((packLanguage) => {
                    return !(packLanguage.salesChannelActive || packLanguage.language.salesChannels.length <= 0);
                });

                if (invalidLanguages.length > 0) {
                    reject(invalidLanguages);
                }
                resolve();
            });
        },

        async resetInvalidUserLanguages() {
            const invalidLocales = await this.fetchInvalidLocaleIds();
            if (!invalidLocales || invalidLocales.length <= 0) {
                return Promise.resolve();
            }

            const currentUser = await this.userService.getUser();
            const invalidUserCriteria = (new Criteria()).addFilter(
                Criteria.equalsAny('localeId', invalidLocales),
            );

            let invalidUsers = await this.userRepository.search(invalidUserCriteria);
            invalidUsers = invalidUsers.reduce((accumulator, user) => {
                user.localeId = this.fallbackLocaleId;

                // If we change the locale of the current logged in user
                if (currentUser.data.id === user.id) {
                    Shopware.Service('localeHelper').setLocaleWithId(user.localeId);
                }

                accumulator.push(user);

                return accumulator;
            }, []);

            return this.userRepository.saveAll(invalidUsers);
        },

        async fetchInvalidLocaleIds() {
            const invalidAdminLanguagesCriteria = (new Criteria())
                .addFilter(Criteria.equals('extensions.swagLanguagePackLanguage.administrationActive', false));
            const invalidAdminLanguages = await this.languageRepository.search(invalidAdminLanguagesCriteria);

            const fallbackLanguageCriteria = (new Criteria()).setIds([Defaults.systemLanguageId]);
            const fallbackLanguage = await this.languageRepository.search(fallbackLanguageCriteria);
            this.fallbackLocaleId = fallbackLanguage.first().localeId;

            return invalidAdminLanguages.map(language => language.localeId);
        },
    },
});

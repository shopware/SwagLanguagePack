import template from './swag-language-pack-settings.html.twig';

const { Component, Defaults } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-language-pack-settings', {
    template,

    inject: [
        'repositoryFactory'
        // 'acl' // ToDo LAN-37 - Implement ACL
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
            const usableLocaleIds = await this.fetchInvalidLocaleIds();
            const userCriteria = (new Criteria()).addFilter(
                Criteria.not('OR', [
                    Criteria.equalsAny('localeId', usableLocaleIds)
                ])
            );

            let invalidUsers = await this.userRepository.search(userCriteria, Shopware.Context.api);
            invalidUsers = invalidUsers.reduce((accumulator, user) => {
                user.localeId = this.fallbackLocaleId;
                accumulator.push(user);

                return accumulator;
            }, []);

            return this.userRepository.saveAll(invalidUsers, Shopware.Context.api);
        },

        async fetchInvalidLocaleIds() {
            const languageCriteria = (new Criteria())
                .addFilter(Criteria.multi('OR', [
                    Criteria.equals('id', Shopware.Defaults.systemLanguageId),
                    Criteria.equalsAny('name', ['English', 'Deutsch'])
                ]));

            const defaultLanguages = await this.languageRepository.search(languageCriteria, Shopware.Context.api);
            const usableLocaleIds = defaultLanguages.map((language) => {
                if (language.id === Defaults.systemLanguageId) {
                    this.fallbackLocaleId = language.localeId;
                }

                return language.localeId;
            });

            const activeSalesChannelPackLanguages = this.packLanguages.filter(
                packLanguage => packLanguage.administrationActive
            );
            usableLocaleIds.push(...activeSalesChannelPackLanguages.map(packLanguage => packLanguage.language.localeId));

            return usableLocaleIds;
        }
    }
});

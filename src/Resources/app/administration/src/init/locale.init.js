const { Criteria } = Shopware.Data;
const { warn } = Shopware.Utils.debug;

const languageRepository = Shopware.Service('repositoryFactory').create('language');
const criteria = (new Criteria())
    .addAssociation('swagLanguagePackLanguage')
    .addAssociation('locale')
    .addFilter(Criteria.not('AND', [Criteria.equals('swagLanguagePackLanguage.administrationActive', false)]));

const resolve = Shopware.Plugin.addBootPromise();

let errorLocale;
languageRepository.search(criteria).then((result) => {
    result.forEach((language) => {
        if (Shopware.Locale.getByName(language.locale.code)) {
            return;
        }

        errorLocale = language.locale.code;
        Shopware.Locale.register(language.locale.code, {});
    });

    resolve();
}).catch(() => {
    let message = 'Unable to register "packLanguages".';

    if (errorLocale !== undefined) {
        message += ` Problems occurred while installing language: ${errorLocale}`;
    }

    warn('SwagLanguagePack', message);
    resolve();
});

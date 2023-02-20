const { Criteria } = Shopware.Data;
const { warn } = Shopware.Utils.debug;

const packLanguageRepository = Shopware.Service('repositoryFactory').create('swag_language_pack_language');
const criteria = (new Criteria())
    .addFilter(Criteria.equals('administrationActive', true))
    .addAggregation(Criteria.terms('locales', 'language.locale.code', null, null, null));

const resolve = Shopware.Plugin.addBootPromise();

let errorLocale;
packLanguageRepository.search(criteria).then((result) => {
    result.aggregations.locales.buckets.forEach(({ key: locale }) => {
        if (Shopware.Locale.getByName(locale) === false) {
            errorLocale = locale;
            Shopware.Locale.register(locale, {});
        }
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

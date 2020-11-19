const locales = [
    'bs-BA',
    'cs-CZ',
    'da-DK',
    'es-ES',
    'fr-FR',
    'id-ID',
    'it-IT',
    'lv-LV',
    'nl-NL',
    'pl-PL',
    'pt-PT',
    'ru-RU',
    'sv-SE'
];

locales.forEach((locale) => {
    if (Shopware.Locale.getByName(locale) === false) {
        Shopware.Locale.register(locale, {});
    }
});

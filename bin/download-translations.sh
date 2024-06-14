function copy_language_files() {
    echo "Copying language files for $1..."
    mkdir -p src/Resources/snippet/core
    cp -R translations/translations/"$1"/Platform/Core/messages.json src/Resources/snippet/core/messages."$1".base.json

    mkdir -p src/Resources/snippet/storefront
    cp -R translations/translations/"$1"/Platform/Storefront/storefront.json src/Resources/snippet/storefront/storefront."$1".json

    mkdir -p src/Resources/app/administration/src/snippet
    cp -R translations/translations/"$1"/Platform/Administration/administration.json src/Resources/app/administration/src/snippet/"$1".json

    for plugin in translations/translations/"$1"/Plugins/*; do
        plugin_name=$(basename "$plugin")

        mkdir -p src/Resources/snippet/"$plugin_name"
        cp -R translations/translations/"$1"/Plugins/"$plugin_name"/Storefront/storefront.json src/Resources/snippet/"$plugin_name"/storefront."$1".json

        mkdir -p src/Resources/app/administration/src/snippet/"$plugin_name"
        cp -R translations/translations/"$1"/Plugins/"$plugin_name"/Administration/administration.json src/Resources/app/administration/src/snippet/"$plugin_name"/"$1".json
    done
}

echo "Downloading translations..."
git clone https://github.com/shopware/translations.git

SUPPORTED_LANGUAGES=$(bin/get-languages)

echo "Supported languages: $SUPPORTED_LANGUAGES"

for language in $SUPPORTED_LANGUAGES; do
    copy_language_files "$language"
done

rm -rf translations

echo "Finished downloading translations!"

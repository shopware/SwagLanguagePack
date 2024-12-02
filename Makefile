.DEFAULT_GOAL := help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help

init:  ## Initialize shopware, install language pack, dump the test database
	cd src/Resources/app/administration \
		&& npm i \
		&& cd ../../../../../../../ \
		&& ./psh.phar init\
		&& php bin/console plugin:install --activate -c SwagLanguagePack\
		&& ./psh.phar init-test-databases\
		&& ./psh.phar storefront:init\
		&& ./psh.phar administration:init
.PHONY: init

administration-fix: ## Run eslint on the administration files
	../../../vendor/shopware/platform/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue --fix src/Resources/app/administration
.PHONY: administration-fix

administration-lint: ## Run eslint on the administration files
	../../../vendor/shopware/platform/src/Administration/Resources/app/administration/node_modules/.bin/eslint --ignore-path .eslintignore --config ../../../vendor/shopware/platform/src/Administration/Resources/app/administration/.eslintrc.js --ext .js,.vue src/Resources/app/administration
.PHONY: administration-lint

eslint: administration-lint ## Synonym to 'administration-lint'
.PHONY: eslint

eslint-fix: administration-fix ## Synonym to 'administration-fix'
.PHONY: eslint-fix

phpunit: ## Run phpunit; Accepts an additional argument "FILTER='search term'"
	composer dump-autoload \
        && ./../../../vendor/bin/phpunit --filter "$(FILTER)"
.PHONY: phpunit

phpunit-coverage: ## Run phpunit with coverage report; Accepts an additional argument "FILTER='search term'"
	composer dump-autoload \
	    && php -d pcov.enabled=1 -d pcov.directory=./.. ./../../../vendor/bin/phpunit --configuration phpunit.xml.dist --log-junit ./../../../build/artifacts/phpunit-language-pack.junit.xml --coverage-clover ./../../../build/artifacts/phpunit-language-pack.clover.xml --coverage-html ./../../../build/artifacts/coverage-language-pack --coverage-text --filter "$(FILTER)"
.PHONY: phpunit-coverage

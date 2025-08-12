# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: static-code-analysis
static-code-analysis: vendor ## Runs a static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --memory-limit=-1

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: check-symfony vendor ## Generates a baseline for static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --generate-baseline=phpstan-default-baseline.neon --memory-limit=-1

.PHONY: tests
tests: vendor
	vendor/bin/phpunit tests

.PHONY: vendor
vendor: composer.json composer.lock ## Installs composer dependencies
	composer install

.PHONY: cs
cs: ## Update Coding Standards
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --diff --verbose

# Use phpcs to reformat code to PSR12
codingstandards:
	phpcbf --colors --standard=PSR12 --extensions=php --ignore=vendor/ src/ 

.PHONY: token
token: ## Refresh token
	EASE_LOGGER=console csas-access-token -t`csas-access-token -l | head -n 1 | awk '{print $$2}'`  -o.env

.PHONY: balance
balance: token ## Balance launch
	cd src ; php -f csas-balance.php

.PHONY: statement-downloader
statement-downloader: token ## Statement Downloader launch
	cd src ; php -f csas-statement-downloader.php

.PHONY: statement-mailer
statement-mailer: token ## Statement Mailer launch
	cd src ; php -f csas-statement-mailer.php

.PHONY: report
report: token ## Transaction Report launch
	cd src ; php -f csas-transaction-report.php



phar:
	phar-composer build .

buildimage:
	docker build -f Containerfile -t spojenet/csas-statement-downloader:latest .

buildx:
	docker buildx build -f Containerfile . --push --platform linux/arm/v7,linux/arm64/v8,linux/amd64 --tag spojenet/csas-statement-downloader:latest

drun:
	docker run --env-file .env spojenet/csas-statement-downloader:latest

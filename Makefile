.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install project's dependencies
	composer install

phpcs: ## Check coding standards with phpcs
	./vendor/bin/phpcs --report=full

phpcbf: ## Fix phpcs errors with phpcbf
	./vendor/bin/phpcbf -w

phpunit: ## Unit tests with phpunit
	./vendor/bin/phpunit

phpstan: ## Statical analysis with phptan
	./vendor/bin/phpstan analyse --memory-limit=512M

test: ## Run all tests
	make phpcs
	make phpstan
	make phpunit

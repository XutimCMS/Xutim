TEST_APP=tests/Application
CONSOLE=$(TEST_APP)/bin/console
PHPUNIT=$(TEST_APP)/vendor/bin/simple-phpunit

# Monorepo management
backup-composer:
	find src -type f -name 'composer.json' -exec mv {} {}.bak \;
.PHONY: backup-composer

restore-composer:
	find src -type f -name 'composer.json.bak' -exec sh -c 'mv "$$0" "$${0%.bak}"' {} \;
.PHONY: restore-composer

merge: restore-composer
	vendor/bin/monorepo-builder merge
.PHONY: merge

# Static analysis
phpstan: vendor/
	vendor/bin/phpstan analyse -c phpstan.neon --no-progress --memory-limit=256M
.PHONY: phpstan

phpstan-baseline: vendor/
	vendor/bin/phpstan analyse -c phpstan.neon --no-progress --generate-baseline
.PHONY: phpstan-baseline

ecs: vendor/
	vendor/bin/ecs check
.PHONY: ecs

ecs-fix: vendor/
	vendor/bin/ecs check --fix
.PHONY: ecs-fix

# Test application setup
init-test: $(TEST_APP)/vendor/
	cd $(TEST_APP) && bin/console importmap:install
	cd $(TEST_APP) && bin/console asset-map:compile
	$(MAKE) init-test-db
.PHONY: init-test

$(TEST_APP)/vendor/: $(TEST_APP)/composer.json
	cd $(TEST_APP) && composer install
	@touch $@

init-test-db:
	APP_ENV=test $(CONSOLE) doctrine:database:drop --force --if-exists
	APP_ENV=test $(CONSOLE) doctrine:database:create
	APP_ENV=test $(CONSOLE) doctrine:schema:create
	APP_ENV=test $(CONSOLE) doctrine:fixtures:load --append
.PHONY: init-test-db

# Testing
test: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT)
.PHONY: test

test-all: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT) --testsuite=all
.PHONY: test-all

test-unit: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT) --testsuite=unit
.PHONY: test-unit

test-functional: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT) --testsuite=functional
.PHONY: test-functional

test-core: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT) --testsuite=core
.PHONY: test-core

test-coverage: $(TEST_APP)/vendor/
	cd tests && $(CURDIR)/$(PHPUNIT) --coverage-html=coverage
.PHONY: test-coverage

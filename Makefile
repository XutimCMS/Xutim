CONSOLE=bin/console

backup-composer: 
	find src -type f -name 'composer.json' -exec mv {} {}.bak \;
.PHONY: local

restore-composer: 
	find src -type f -name 'composer.json.bak' -exec sh -c 'mv "$$0" "$${0%.bak}"' {} \;
.PHONY: local

merge: restore-composer
	vendor/bin/monorepo-builder merge
.PHONY: merge

phpstan: vendor/ phpstan.neon
	vendor/bin/phpstan analyse -c phpstan.neon --no-progress --memory-limit=256M
.PHONY: phpstan

phpstan-baseline: vendor/ phpstan.neon
	vendor/bin/phpstan analyse -c phpstan.neon --no-progress --generate-baseline
.PHONY: phpstan-baseline

ecs: vendor/ ecs.php
	vendor/bin/ecs check
.PHONY: ecs

ecs-fix: vendor/ ecs.php
	vendor/bin/ecs check --fix
.PHONY: ecs-fix

test: vendor/
	vendor/bin/simple-phpunit
.PHONY: test

init-test-db: vendor/
	APP_ENV=test $(CONSOLE) doctrine:database:drop --force --if-exists
	APP_ENV=test $(CONSOLE) doctrine:database:create
	APP_ENV=test $(CONSOLE) doctrine:migrations:migrate --no-interaction
	APP_ENV=test $(CONSOLE) doctrine:fixtures:load --append
	APP_ENV=test $(CONSOLE) app:init
.PHONY: init-test-db

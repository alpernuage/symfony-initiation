include .env
export

DOCKER_COMPOSE = docker compose
EXEC = $(DOCKER_COMPOSE) exec
CONTAINER_PHP = $(EXEC) php
PHP = $(CONTAINER_PHP) php
CONSOLE = $(PHP) bin/console
COMPOSER = $(PHP) composer
BRANCH := $(shell git rev-parse --abbrev-ref HEAD)

# 🎨 Colors
RED := $(shell tput -Txterm setaf 1)
GREEN := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
BLUE := $(shell tput -Txterm setaf 4)
ORANGE=$(shell tput setaf 172)
LIME_YELLOW=$(shell tput setaf 190)
RESET=$(shell tput sgr0)
BOLD=$(shell tput bold)
REVERSE=$(shell tput smso)

## —— 📦 Install dependencies ——
.PHONY: vendor
vendor: ## Install PHP dependencies
vendor: .env.local
	$(COMPOSER) install

## —— 🔥 Project ——
.env.local: ## 📄📄 Create or update .env.local file
.env.local: .env
	@if [ -f .env.local ]; then \
		if ! cmp -s .env .env.local; then \
			echo "${LIME_YELLOW}ATTENTION: ${RED}${BOLD}.env file and .env.local are different, check the changes bellow:${RESET}${REVERSE}"; \
			diff -u .env .env.local | grep -E "^[\+\-]"; \
			echo "${RESET}---\n"; \
			echo "${LIME_YELLOW}ATTENTION: ${ORANGE}This message will only appear once if the .env file is updated again.${RESET}"; \
			touch .env.local; \
			exit 1; \
		fi \
	else \
		cp .env .env.local; \
		echo "${GREEN}.env.local file has been created."; \
		echo "${ORANGE}Modify it according to your needs and continue.${RESET}"; \
		exit 1; \
	fi

.PHONY: install
install: ## 🚀 Project installation
install: .env.local build start vendor assets
	@symfony server:start --tls
	@echo "${BLUE}The application is available at the url: $(SERVER_NAME)$(RESET)";

## —— 🖥️ Console ——
.PHONY: console
console: ## Execute console command to accept arguments that will complete the command
	$(CONSOLE) $(filter-out $@,$(MAKECMDGOALS))

## —— 🎩 Composer ——
.PHONY: composer
composer: ## Execute composer command
	$(COMPOSER)

## —— Assets ——
.PHONY: assets
assets: ## Install assets
	npm install
	npm run build

## —— 🐳 Docker ——
.PHONY: build
build: ## 🏗️ Build the container
build: docker-compose.override.yml
	$(DOCKER_COMPOSE) build --build-arg APP_ENV=$(APP_ENV)

docker-compose.override.yml: docker-compose.override.yml.dist
	@if [ -f docker-compose.override.yml ]; then \
		echo '${YELLOW}/!!!\ "docker-compose.override.yml.dist" has changed. You may want to update your copy accordingly (this message will only appear once).$(RESET)'; \
		touch docker-compose.override.yml; \
		exit 1; \
	else \
		cp docker-compose.override.yml.dist docker-compose.override.yml; \
		echo "cp docker-compose.override.yml.dist docker-compose.override.yml"; \
		echo "${YELLOW}Modify it according to your needs and rerun the command.$(RESET)"; \
		exit 1; \
	fi

#docker-compose.override.yml: ## 📄📄 Create or update docker-compose.override.yml file
#docker-compose.override.yml: docker-compose.override.yml.dist
#	@if [ -f docker-compose.override.yml ]; then \
#		if ! cmp -s docker-compose.override.yml.dist docker-compose.override.yml; then \
#			echo "${LIME_YELLOW}ATTENTION: ${RED}${BOLD}docker-compose.override.yml.dist file and docker-compose.override.yml are different, check the changes bellow:${RESET}${REVERSE}"; \
#			diff -u docker-compose.override.yml.dist docker-compose.override.yml | grep -E "^[\+\-]"; \
#			echo "${RESET}---\n"; \
#			echo "${LIME_YELLOW}ATTENTION: ${ORANGE}This message will only appear once if the docker-compose.override.yml.dist file is updated again.${RESET}"; \
#			touch docker-compose.override.yml; \
#			exit 1; \
#		fi \
#	else \
#		cp docker-compose.override.yml.dist docker-compose.override.yml; \
#		echo "${GREEN}docker-compose.override.yml file has been created."; \
#		echo "${ORANGE}Modify it according to your needs and continue.${RESET}"; \
#		exit 1; \
#	fi

.PHONY: start
start: ## ▶️ Start the containers
start: .env.local docker-compose.override.yml
	$(DOCKER_COMPOSE) up -d --remove-orphans

.PHONY: stop
stop: ## ⏹️ Stop the containers
	$(DOCKER_COMPOSE) stop

.PHONY: restart
restart: ## 🔄 restart the containers
restart: stop start

.PHONY: kill
kill: ## ❌ Forces running containers to stop by sending a SIGKILL signal
	$(DOCKER_COMPOSE) kill

.PHONY: down
down: ## ⏹️🧹 Stop containers and clean up resources
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

.PHONY: reset
reset: ## Stop and start a fresh install of the project
reset: kill down build start

## —— 🔨 Tools ——
.PHONY: cache
cache: ## 🧹 Clear Symfony cache
	$(CONSOLE) cache:clear

.PHONY: cache-test
cache-test: ## 🧹 Clear Symfony cache for test environment
	$(CONSOLE) cache:clear --env=test

## —— 🔍 PHPStan ——
.PHONY: phpstan
phpstan: ## PHP Static Analysis Tool (https://github.com/phpstan/phpstan)
	$(PHP) vendor/bin/phpstan --memory-limit=-1 analyse src

## —— 🔧 PHP CS Fixer ——
.PHONY: fix-php-cs
fix-php-cs: ## PhpCsFixer (https://cs.symfony.com/)
	$(PHP) vendor/bin/php-cs-fixer fix --verbose

## —— 🗄️ Database ——
.PHONY: migration
migration: ## 🔀 Generate a new Doctrine migration
	$(CONSOLE) doctrine:migrations:diff --formatted

.PHONY: migrate
migrate: ## Run migrations
	$(CONSOLE) doctrine:migration:migrate --no-interaction

.PHONY: database
database: ## 📊 Create and migrate the database schema
	$(CONSOLE) doctrine:database:drop --force || true
	$(CONSOLE) doctrine:database:create
	$(CONSOLE) doctrine:migrations:migrate -n

.PHONY: fixtures
fixtures: ## Load database fixtures
	$(CONSOLE) doctrine:fixtures:load -n

## —— ✅ Testing ——
.PHONY: test-database
test-database: ## Prepare the test database
	$(CONSOLE) doctrine:database:drop --force --env=test || true
	$(CONSOLE) doctrine:database:create --env=test
	$(CONSOLE) doctrine:migrations:migrate -n --env=test

.PHONY: test-fixtures
test-fixtures: ## Load test fixtures
test-fixtures: test-database
	$(CONSOLE) doctrine:fixtures:load -n --env=test

.PHONY: test
test: ## Run tests
	$(PHP) vendor/bin/simple-phpunit

test-filter: ## Run filtered tests
	$(PHP) vendor/bin/simple-phpunit --filter $(filter-out $@,$(MAKECMDGOALS))

testdox: ## Run tests with testdox output for clearer test result summary (https://docs.phpunit.de/en/10.2/attributes.html#testdox)
	$(PHP) vendor/bin/simple-phpunit --testdox

## —— 🐱 Git ——
.PHONY: pull
pull: ## Run git pull command on current branch
	git pull origin $(BRANCH)

.PHONY: push
push: ## Run git push command on current branch
	git push origin $(BRANCH)

## —— 🛠️ Others ——
.PHONY: open
open: ## Open the project in the browser
	@echo "${GREEN}Opening https://$(SERVER_NAME)"
	@open https://$(SERVER_NAME)

.DEFAULT_GOAL := help
.PHONY: help
help: ## Describe targets
	@grep -E '(^[a-z0-9A-Z_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

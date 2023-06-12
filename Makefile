include .env
export

DOCKER_COMPOSE = docker compose
EXEC = $(DOCKER_COMPOSE) exec
PHP = $(EXEC) php
CONSOLE = $(PHP) bin/console
CONTAINER_PHP = alper-initiation-php
COMPOSER = $(PHP) composer
BRANCH := $(shell git rev-parse --abbrev-ref HEAD)

# 🎨 Colors
GREEN := $(shell tput -Txterm setaf 2)
RED := $(shell tput -Txterm setaf 1)
YELLOW := $(shell tput -Txterm setaf 3)
RESET=\033[0m

## —— 📦 Install dependencies ——
.PHONY: composer-install
composer-install: ## Install PHP dependencies
	$(COMPOSER) install

## —— 🔥 Project ——
.PHONY: env
env: ## 📄📄 Create .env.local file
	@if [ -f .env.local ]; then \
		echo 'File already exists.'; \
		echo '${YELLOW}/!!!\ ".env.local" already exists. Please update your copy accordingly.$(RESET)'; \
	else \
		cp .env .env.local; \
		echo 'cp .env .env.local'; \
		echo '${YELLOW}.env.local file created. Please modify it according to your needs and rerun the command.$(RESET)'; \
	fi

.PHONY: install
install: ## 🚀 Project installation
install: ssl build start vendor
	echo "${YELLOW}The application is available at the url: SERVER_NAME$(RESET)";

## —— 🎩 Composer ——
.PHONY: composer
composer: ## Execute composer command
	$(COMPOSER)

## —— 🔐 TLS certificate ——
.PHONY: ssl
ssl: ## Create tls certificates via mkcert library: https://github.com/FiloSottile/mkcert
ssl:
	rm -rf devops/caddy/certs/*
	cd ./devops/caddy/certs && mkcert $(SERVER_NAME)

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

.PHONY: start
start: ## ▶️ Start the containers
start: docker-compose.override.yml
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

## —— 🔍 PHPStan ——
.PHONY: phpstan
phpstan: ## PHP Static Analysis Tool (https://github.com/phpstan/phpstan)
	$(PHP) vendor/bin/phpstan

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

## —— 🐱 Git ——
.PHONY: pull
pull: ## Run git pull command on current branch
	git pull origin $(BRANCH)

.PHONY: push
push: ## Run git push command on current branch
	git push origin $(BRANCH)

## —— 🛠️ Others ——
.DEFAULT_GOAL := help
.PHONY: help
help: ## Describe targets
	@grep -E '(^[a-z0-9A-Z_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

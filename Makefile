# Created: 2018-01-01 00:00:00

# Makefile for the project
# ------------------------
# This file is used to build the project.
# It is used to build the project in container with docker_compose file.
#
COLOR_RESET   = \033[0m
COLOR_INFO    = \033[32m
COLOR_COMMENT = \033[33m

CURRENT_DIR := $(shell pwd)
include .env
.DEFAULT_GOAL := help

DOCKER_EXEC := docker run -it --rm --name php-container -v $(CURRENT_DIR)/application:/opt/application -w /opt/application composer:2.8.4


DOCKER_EXEC_POSTGRES := $(docker_exec) $(CONTAINER_POSTGRES)
## bash console
console:
	$(DOCKER_EXEC) bash

## composer install
composer-install:
	$(DOCKER_EXEC) composer install

## security
security:
	$(DOCKER_EXEC) ${security-sast} src
	$(DOCKER_EXEC) symfony security:check

## init db
init-db:
	$(DOCKER_EXEC) php bin/console doctrine:database:drop --force --if-exists
	$(DOCKER_EXEC) php bin/console doctrine:database:create
	$(DOCKER_EXEC_POSTGRES) sh -c "psql -Usuperviseur Superviseur < /root/superviseur_v3_dump.sql"

init-test-db:
	$(DOCKER_EXEC) php bin/console doctrine:database:drop --env=test --if-exists --force
	$(DOCKER_EXEC) php bin/console doctrine:database:create --env=test
	$(DOCKER_EXEC) php bin/console doctrine:migrations:migrate --env=test --no-interaction
	$(DOCKER_EXEC) php bin/console doctrine:fixtures:load --env=test --no-interaction


## superviseur-v4 install
install: composer-install init-db generate-jwt-keys migration

## superviseur-v4 migration
migration:
	$(DOCKER_EXEC) php bin/console doctrine:migrations:migrate --no-interaction

## superviseur-v4 migration generate
migration-generate:
	$(DOCKER_EXEC) php bin/console doctrine:migrations:generate --no-interaction

## superviseur-v4 functional tests
functional-tests: init-test-db
	$(DOCKER_EXEC) ${test}

## launch application in dev mode
run:
	@echo "run"
	CURRENT_DIR=$(CURRENT_DIR) docker-compose --env-file $(CURRENT_DIR)/.env -f $(CURRENT_DIR)/automation/local/docker-compose.yml up

generate-jwt-keys:
	$(DOCKER_EXEC) php bin/console lexik:jwt:generate-keypair

load-instances:
	$(DOCKER_EXEC) php bin/console s:c-i





help:
	@printf "${COLOR_COMMENT}Usage:${COLOR_RESET}\n"
	@printf " make [target]\n\n"
	@printf "${COLOR_COMMENT}Available targets:${COLOR_RESET}\n"
	@awk '/^[a-zA-Z\-_0-9\.@]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf " ${COLOR_INFO}%-16s${COLOR_RESET} %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

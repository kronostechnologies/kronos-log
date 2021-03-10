BASE_DIR := $(dir $(realpath $(firstword $(MAKEFILE_LIST))))
OS_TYPE := $(shell uname -s)
USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)
MKDIR_P = mkdir -p
DOCKER_PHP = docker run -it --rm \
	-v $(BASE_DIR):/home/circleci/project \
    -v ~/.cache:/home/circleci/.cache --user "${USER_ID}:${GROUP_ID}" \
    --workdir /home/circleci/project \
    -e HOME=/home/circleci \
    ghcr.io/kronostechnologies/php:7.2-node

.PHONY: all setup check psalm psalm.ignoreBaseline psalm.updateBaseline test

all: setup check test

setup:
	@composer install

check: psalm

psalm:
	@${DOCKER_PHP} ./vendor/bin/psalm $(PSALM_ARGS)

psalm.ignoreBaseline:
	@${DOCKER_PHP} ./vendor/bin/psalm --ignore-baseline $(PSALM_ARGS)

psalm.updateBaseline:
	@${DOCKER_PHP} ./vendor/bin/psalm --no-diff --no-cache --update-baseline

test:
	@./vendor/bin/phpunit

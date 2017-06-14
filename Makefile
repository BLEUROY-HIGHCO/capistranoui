.PHONY: all build up restart clean start stop php php-cli mysql npm-install composer-install apache clear-cache mig-diff restart

# Executable
APACHE=docker-compose exec --user www-data apache
PHP=docker-compose exec --user www-data php
PHP_CLI=docker-compose exec -T --user www-data php
PHP_ROOT_CLI=docker-compose exec -T --user root php
MYSQL=docker-compose exec --user mysql mysql
MYSQL_CLI=docker-compose exec -T --user mysql mysql
SYNC=docker-compose exec --user www-data sync
SYNC_ROOT_CLI=docker-compose exec -T --user root sync

# Container name
CONTAINER_MYSQL=capistranoui_mysql
CONTAINER_PHP=capistranoui_php
CONTAINER_SYNC=capistranoui_sync


# Entries
all: build up

build:
	docker-compose build

start: clean up watch

up:
	sudo ifconfig lo0 alias 10.254.254.254
	if [ ! -f ./app/config/parameters.yml ]; \
	then \
	cp ./app/config/parameters.yml.dist ./app/config/parameters.yml; \
	fi;
	docker-compose up -d
	docker cp . $(CONTAINER_SYNC):/var/www/
	$(SYNC_ROOT_CLI) chown -R www-data:www-data /var/www

stop:
	docker-compose stop

restart: stop start

mysql:
	$(MYSQL) /bin/bash

composer-install:
	$(PHP_CLI) composer config -g github-oauth.github.com d70131faa38a890f5cf0488ee2c94de999088aed
	$(PHP_CLI) composer install
	docker cp $(CONTAINER_PHP):/var/www/vendor/. vendor
	docker cp $(CONTAINER_PHP):/var/www/composer.json ./composer.json
	docker cp $(CONTAINER_PHP):/var/www/composer.lock ./composer.lock
	docker cp $(CONTAINER_PHP):/var/www/app/config/parameters.yml ./app/config/parameters.yml

npm-install:
	$(PHP_CLI) bash -c "yarn install"

watch:
	$(PHP_CLI) /usr/local/bin/watch.sh &

clean:
	docker-compose rm -f

apache:
	$(APACHE) /bin/bash

sync:
	$(SYNC) /bin/bash

php:
	$(PHP) /bin/bash

php-cli:
	$(PHP) bash -c "${ARGS}"

mig-diff:
	$(PHP_CLI) bin/console d:m:d

clear-cache:
	$(PHP_CLI) rm -rf var/cache/*

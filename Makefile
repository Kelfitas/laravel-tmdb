all: install

set-key:
	php artisan key:generate

migrate:
	php artisan migrate

install-composer: composer.json
	composer install --no-scripts $(COMPOSERARGS)

install: install-composer

production: COMPOSERARGS=--no-dev
production: install migrate

dev: install migrate

dev-run:
	php artisan serve

init-dev: dev set-key
	php artisan api:get:genres

init-prod: production set-key
	php artisan api:get:genres
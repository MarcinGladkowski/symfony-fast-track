tests:
	docker-compose exec php-fpm php bin/console doctrine:database:drop --force --env=test || true
	docker-compose exec php-fpm php bin/console doctrine:database:create --env=test
	docker-compose exec php-fpm php bin/console doctrine:migrations:migrate -n --env=test
	docker-compose exec php-fpm php bin/console doctrine:fixtures:load -n --env=test
	docker-compose exec php-fpm php bin/phpunit $@

build-php:
	docker-compose build php-fpm

npm-install:
	npm install --registry=https://registry.npmjs.org/

run-fe:
	npm run dev-server

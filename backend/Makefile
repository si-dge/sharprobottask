install:
	composer install
migrate:
	psql "$(DB_DSN)" -f database/schema.sql
seed:
	composer db:seed
orm-schema:
	php bin/doctrine-schema.php
test:
	composer test -- --exclude-group api
analyse:
	composer analyse
cs-check:
	composer cs-check
cs-fix:
	composer cs-fix
test-api:
	docker compose -f ../docker-compose.yml stop worker && \
	curl --fail --silent --show-error -X POST $(or $(IMPORT_API_URL),http://localhost:8080/api)/database/reset && \
	docker compose -f ../docker-compose.yml start worker && \
	docker compose -f ../docker-compose.yml exec -T \
		-e IMPORT_API_URL=$(or $(IMPORT_API_URL),http://localhost:8080/api) \
		-e IMPORT_TEST_FILE=/import_example.xlsx app \
		vendor/bin/phpunit tests/ImportApiTest.php --group api

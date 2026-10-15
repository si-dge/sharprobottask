# Импорт товаров

Slim API + Angular frontend для импорта товаров из XLSX, просмотра каталога и обработки заданий через Kafka.

## Требования

- Docker Engine
- Docker Compose v2

Проверка установки:

```bash
docker --version
docker compose version
```

## Сборка и запуск

Из корня проекта:

```bash
./build.sh
./setup.sh
```

После запуска:

- frontend и API через nginx: http://localhost
- API напрямую: http://localhost:8080
- проверка API: http://localhost/api/health
- Swagger UI: http://localhost/docs/swagger.html

Проверить состояние контейнеров:

```bash
docker compose ps
docker compose logs -f app worker
```

Остановить приложение:

```bash
./shutdown.sh
```

Остановить приложение и удалить данные PostgreSQL:

```bash
docker compose down -v
```

## CI/CD

Workflow [`.github/workflows/ci.yml`](.github/workflows/ci.yml) запускает сборку Docker-контейнеров, PHPStan, PHP CS Fixer, загрузку и проверку fixtures для `products`, `product_attributes` и `product_images`, backend PHPUnit-тесты, сборку frontend и Playwright e2e-тесты.

Production deploy находится в [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml) и запускается вручную через **Actions -> Deploy -> Run workflow**. Перед запуском в GitHub Environment `production` задайте secrets `SERVER_HOST`, `SERVER_USER`, `SERVER_PATH`, `SSH_PRIVATE_KEY`, `SERVER_KNOWN_HOSTS`; `SERVER_PORT` можно задать как secret, а `HEALTHCHECK_URL` как variable. Deploy сначала вызывает CI и продолжает только после успешного прохождения всех проверок.

## Основные страницы

- Каталог товаров: http://localhost/products
- Импорт XLSX: http://localhost/import
- Задания worker: http://localhost/worker-tasks

## API

- `GET /api/health` - проверка API
- `POST /api/login` - получение JWT (`admin` / `admin` по умолчанию для локального запуска)
- `GET /api/products` - список товаров
- `GET /api/products/{id}` - карточка товара
- `POST /api/imports` - загрузка XLSX в очередь импорта
- `GET /api/imports/{id}` - статус импорта
- `GET /api/worker/tasks` - список заданий worker
- `POST /api/database/reset` - очистка товаров и заданий

Пример проверки:

```bash
curl http://localhost/api/health
curl -X POST http://localhost:8080/api/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"admin"}'
curl 'http://localhost:8080/api/products?page=1&limit=20'
```

Все API-маршруты, кроме `/api/health` и `/api/login`, защищены JWT. Передавайте токен в заголовке `Authorization: Bearer <token>`. Для Docker задайте `JWT_SECRET`, `AUTH_USERNAME` и `AUTH_PASSWORD` через `.env`.

## Swagger / OpenAPI

Спецификация API находится в [docs/openapi.yaml](docs/openapi.yaml).

В Docker Swagger UI доступен по адресу http://localhost/docs/swagger.html.
Каталог документации защищен Basic Auth: логин `admin`, пароль `admin`.
Спецификация загружается из http://localhost/docs/openapi.yaml.

Открыть спецификацию в Swagger Editor:

https://editor.swagger.io/

В Swagger Editor выберите **File > Import File** и укажите `docs/openapi.yaml`.

Проверить OpenAPI-файл:

```bash
docker run --rm -v "$PWD/docs:/spec:ro" \
  swaggerapi/swagger-cli validate /spec/openapi.yaml
```

## Локальная разработка frontend

```bash
cd frontend
npm install
npm start -- --port 4200
```

Frontend будет доступен по адресу http://localhost:4200. Dev server проксирует `/api` на `http://localhost:8080`.
В режиме разработки Swagger UI доступен по адресу http://localhost:4200/docs/swagger.html.
Для загрузки данных frontend API должен быть запущен на `http://localhost:8080`.

Сборка frontend:

```bash
cd frontend
npm run build
```

## Локальная разработка backend

```bash
cd backend
composer install
php -S 0.0.0.0:8080 -t public
```

Для полноценного запуска backend нужны PostgreSQL и Kafka. Проще запускать зависимости через Docker Compose.

## Тестовые данные

После применения схемы PostgreSQL можно заполнить демонстрационными товарами, атрибутами и изображениями:

```bash
cd backend
composer db:seed
```

Doctrine fixture находится в `backend/database/fixtures/ProductsFixture.php`, использует настройки `DB_DSN`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` и `DB_PASSWORD` и при повторном запуске пересоздает данные трех таблиц товаров.

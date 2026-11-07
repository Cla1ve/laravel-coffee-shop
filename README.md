# Зерно: интернет-магазин кофе и чая

Курсовая работа по МДК.09.01. Тема: разработка веб-приложения «Интернет-магазин» на Laravel.

Что есть в магазине:
- каталог с поиском, фильтром по категории и цене, сортировкой и страницами;
- карточка товара с отзывами и оценками;
- корзина (работает и без входа), оформление заказа после входа;
- личный кабинет: свои заказы, отмена нового заказа;
- админ-панель: товары (с фото), категории, заказы со сменой статуса, пользователи, сводка;
- REST API с токенами (Laravel Sanctum), описание в OpenAPI.

Роли: гость, покупатель, администратор.

## Что нужно

- PHP 8.3 или новее с расширениями pdo_sqlite, sqlite3, mbstring, openssl, fileinfo, gd, curl, zip
- Composer

Другой сервер БД не нужен, используется файл SQLite.

## Запуск

```
composer install
copy .env.example .env
php artisan key:generate
type nul > database\database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Сайт откроется на http://localhost:8000. В Linux вместо `copy` и `type nul >` команды `cp .env.example .env` и `touch database/database.sqlite`.

## Учебные логины

| Роль | Почта | Пароль |
|---|---|---|
| Администратор | admin@zerno.test | Admin2026! |
| Покупатель | ivan@zerno.test | User2026! |
| Покупатель | olga@zerno.test | User2026! |

## Тесты

```
php artisan test
```

Всего 84 теста (юнит, функциональные, API). Тесты идут на базе в памяти, рабочую базу не трогают.

## API

Описание в `public/docs/openapi.yaml`. После запуска сервера открывается на странице http://localhost:8000/docs/ (Swagger UI подключается с интернета). Коллекция для Postman: `docs/zerno.postman_collection.json`.

Основные адреса: `POST /api/login`, `GET /api/products`, `GET /api/products/{id}`, `POST /api/orders`, `GET /api/orders`.

## Структура

```
app/Http/Controllers      контроллеры сайта, Admin/ (админка) и Api/
app/Http/Middleware       IsAdmin: пускает только администратора
app/Services              Cart (корзина в сессии) и OrderService (создание заказа)
app/Models                модели Eloquent
database/migrations       миграции
database/seeders          тестовые данные
resources/views           шаблоны Blade (Bootstrap 5)
routes/web.php, api.php   маршруты
tests/                    автотесты
docs/                     схемы, коллекция Postman, источники фото, SQL схемы
Курсовая работа.docx      пояснительная записка
```

Фото товаров с сайта pexels.com, номера записаны в `docs/photo_sources.md`.

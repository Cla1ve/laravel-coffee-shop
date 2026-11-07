-- Структура БД магазина Зерно (SQLite), получена командой .schema после php artisan migrate

CREATE TABLE IF NOT EXISTS "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "role" varchar not null default 'user', "phone" varchar);

CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");

CREATE TABLE IF NOT EXISTS "personal_access_tokens" ("id" integer primary key autoincrement not null, "tokenable_type" varchar not null, "tokenable_id" integer not null, "name" text not null, "token" varchar not null, "abilities" text, "last_used_at" datetime, "expires_at" datetime, "created_at" datetime, "updated_at" datetime);

CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens" ("tokenable_type", "tokenable_id");

CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens" ("token");

CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens" ("expires_at");

CREATE TABLE IF NOT EXISTS "categories" ("id" integer primary key autoincrement not null, "name" varchar not null, "slug" varchar not null, "created_at" datetime, "updated_at" datetime);

CREATE UNIQUE INDEX "categories_name_unique" on "categories" ("name");

CREATE UNIQUE INDEX "categories_slug_unique" on "categories" ("slug");

CREATE TABLE IF NOT EXISTS "products" ("id" integer primary key autoincrement not null, "category_id" integer not null, "name" varchar not null, "slug" varchar not null, "description" text not null, "price" numeric not null, "stock" integer not null default '0', "image" varchar, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("category_id") references "categories"("id") on delete restrict);

CREATE INDEX "products_name_index" on "products" ("name");

CREATE UNIQUE INDEX "products_slug_unique" on "products" ("slug");

CREATE TABLE IF NOT EXISTS "orders" ("id" integer primary key autoincrement not null, "user_id" integer not null, "status" varchar not null default 'new', "total" numeric not null, "customer_name" varchar not null, "phone" varchar not null, "address" varchar not null, "comment" varchar, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade);

CREATE INDEX "orders_status_index" on "orders" ("status");

CREATE TABLE IF NOT EXISTS "order_items" ("id" integer primary key autoincrement not null, "order_id" integer not null, "product_id" integer not null, "quantity" integer not null, "price" numeric not null, "created_at" datetime, "updated_at" datetime, foreign key("order_id") references "orders"("id") on delete cascade, foreign key("product_id") references "products"("id") on delete restrict);

CREATE TABLE IF NOT EXISTS "reviews" ("id" integer primary key autoincrement not null, "user_id" integer not null, "product_id" integer not null, "rating" integer not null, "text" text not null, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id") on delete cascade, foreign key("product_id") references "products"("id") on delete cascade);

CREATE UNIQUE INDEX "reviews_user_id_product_id_unique" on "reviews" ("user_id", "product_id");

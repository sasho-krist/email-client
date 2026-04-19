# Имейл клиент (Browser Email Client)

Уеб приложение на **[Laravel](https://laravel.com)** за четене и управление на поща през **IMAP**, с профил по потребител, множество имейл акаунти и **REST API** (Laravel Sanctum). Интерфейсът е главно на **български**, с опция за тъмна тема.

**Репозиторий:** [github.com/sasho-krist/email-client](https://github.com/sasho-krist/email-client)

---

## Снимки от екрана

Интерфейсът е с **тъмна тема** и на **български**. Файловете са в [`public/screenshots/`](public/screenshots/).

### Начална страница

<p align="center">
  <img src="public/screenshots/7.png" alt="Начало — описание на приложението и основни възможности" width="780" />
</p>

### Поща — входящи

<p align="center">
  <img src="public/screenshots/1.png" alt="Входящи — списък съобщения" width="780" />
</p>

### Поща — изходящи

<p align="center">
  <img src="public/screenshots/2.png" alt="Изходящи — изпратени съобщения" width="780" />
</p>

### Поща — нежелана поща

<p align="center">
  <img src="public/screenshots/3.png" alt="Нежелана поща (спам)" width="780" />
</p>

### Настройки — сървър (IMAP/SMTP)

<p align="center">
  <img src="public/screenshots/4.png" alt="Настройки: входящ и изходящ сървър, Gmail указания" width="780" />
</p>

### Настройки — подпис (HTML / TinyMCE)

<p align="center">
  <img src="public/screenshots/5.png" alt="Настройки: имейл подпис с rich text редактор" width="780" />
</p>

### Имейл акаунти

<p align="center">
  <img src="public/screenshots/6.png" alt="Списък имейл акаунти, добавяне и действия" width="780" />
</p>

### Упътване за REST API

<p align="center">
  <img src="public/screenshots/8.png" alt="Страница API: базов URL, Sanctum, login/logout" width="780" />
</p>

---

## Преглед

- Вход и регистрация (Laravel Breeze), потвърждаване на имейл, профил с аватар.
- Свързване към пощенски акаунти чрез **автоматично откриване** на IMAP/SMTP (Thunderbird/autoconfig и известни доставчици) или ръчни настройки.
- Папки по роля: **Входящи**, **Изходящи**, **Спам**, **Кошче** — с автоматично разпознаване на системни папки при Gmail/Google и други доставчици.
- Настройки за подпис (HTML/TinyMCE), поведение при изтриване, групиране на входящи и др.
- Вградено упътване за REST API (`/api-docs`).
- Страници: поверителност, общи условия, ЧЗВ.

---

## Технологии

| Компонент | Бележки |
|-----------|---------|
| PHP | ^8.3 |
| Laravel | ^13 |
| Автентикация API | Laravel Sanctum |
| IMAP | [webklex/laravel-imap](https://github.com/Webklex/laravel-imap) |
| Тестове | PHPUnit |
| Качество на код | Laravel Pint, PHPStan + Larastan |
| Frontend | Blade, Tailwind CSS, Alpine.js, Vite |

---

## Изисквания

- **PHP** 8.3+ с разширения: `openssl`, `pdo_sqlite` и/или `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- **[Composer](https://getcomposer.org/)** 2.x
- **Node.js** 18+ и npm (за събиране на фронтенда)
- **База данни:** SQLite (удобно за локално) или MySQL/MariaDB — задава се в `.env`

---

## Клониране

```bash
git clone https://github.com/sasho-krist/email-client.git
cd email-client
```

---

## Инсталация

1. **Зависимости на PHP**

   ```bash
   composer install
   ```

2. **Околна среда**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Редактирайте `.env`: `APP_NAME`, `APP_URL`, `DB_*`, при нужда `MAIL_*` (за системни имейли — вижте коментарите в `.env.example` и секцията `extra.smtp-server` в `composer.json`).

3. **База данни**

   За SQLite (пример):

   ```bash
   touch database/database.sqlite
   ```

   В `.env`: `DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`

   След това:

   ```bash
   php artisan migrate
   ```

4. **Фронтенд**

   ```bash
   npm ci
   npm run build
   ```

   За разработка с горещо презареждане: `npm run dev` (и в друг терминал `php artisan serve`).

5. **Стартиране**

   ```bash
   php artisan serve
   ```

   Отворете приложението на адреса, който показва Artisan (обикновено `http://127.0.0.1:8000`).

---

## Тестове и качество на кода

```bash
php artisan test
vendor/bin/pint
vendor/bin/phpstan analyse --memory-limit=512M
```

За проекта е конфигуриран **`phpunit.xml`** с SQLite за тестова база — не ползвайте production MySQL при пускане на тестовете без да прегледате настройките.

---

## REST API

Базов префикс: **`/api/v1`** (напр. `https://вашият-домейн/api/v1`).

Удостоверяване: след **`POST /api/v1/login`** ползвайте заглавка:

`Authorization: Bearer <token>` и препоръчително `Accept: application/json`.

### Основни маршрути

| Метод | Път | Описание |
|-------|-----|----------|
| `POST` | `/api/v1/login` | Вход — връща токен Sanctum |
| `POST` | `/api/v1/logout` | Изход (requires auth) |
| `GET` | `/api/v1/user` | Текущ потребител |
| `POST` | `/api/v1/mail/discover` | Откриване на IMAP/SMTP по имейл |
| `GET` | `/api/v1/email-accounts` | Списък имейл акаунти |
| `POST` | `/api/v1/email-accounts` | Създаване на акаунт |
| `DELETE` | `/api/v1/email-accounts/{account}` | Изтриване на акаунт |
| `GET` | `/api/v1/email-accounts/{account}/folders/{folder}/messages` | Съобщения в папка (`inbox`, `sent`, `spam`, `trash`) |
| `GET` | `/api/v1/email-accounts/{account}/folders/{folder}/messages/{uid}` | Едно съобщение по UID |
| `DELETE` | `/api/v1/email-accounts/{account}/folders/{folder}/messages/{uid}` | Изтриване/обработка на съобщение |
| `PATCH` | `/api/v1/mail/settings` | Настройки на пощата |

Пълно описание на полетата и примери има на страницата **`/api-docs`** в работещото приложение.

---

## Composer скриптове (откъси)

| Скрипт | Действие |
|--------|----------|
| `composer run setup` | Инсталация, `.env`, ключ, миграции, npm install и build |
| `composer run dev` | Сървър + опашка + логове + Vite (вижте `composer.json`) |
| `composer run test` | Изчистване на кеш конфигурация и `php artisan test` |

---

## Лиценз

В `composer.json` проектът е обозначен с **MIT** — съответствайте на условията на Laravel и използваните пакети.

---

## Благодарности

- [Laravel](https://laravel.com)
- [Webklex IMAP](https://github.com/Webklex/laravel-imap)

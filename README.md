# Sword

A Laravel-based Bible study application.

## Local Development Setup (Laravel Valet)

### Prerequisites

- PHP 8.1+
- Composer
- Node.js & npm
- [Laravel Valet](https://laravel.com/docs/valet)

### Installation Steps

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```

2. **Install Node dependencies:**
   ```bash
   npm install
   ```

   > **Note:** The project uses these key frontend packages: `jquery`, `datatables.net`, `chart.js`, and `moment`. These are listed in `package.json` and installed automatically.

3. **Create environment file:**
   ```bash
   cp .env.example .env
   # Or create a new .env file with the following contents:
   ```

4. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

5. **Create the SQLite database file:**
   ```bash
   touch database/database.sqlite
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Link Valet (if not already in a Valet-parked directory):**
   ```bash
   # If ~/sites is already parked with Valet, skip this step
   # Otherwise, link the project:
   valet link sword
   ```

8. **Build frontend assets:**
   ```bash
   # For development (with hot reload):
   npm run dev
   
   # For production:
   npm run build
   ```

### Running the Application

#### Option 1: Standard Valet (Recommended)
Access the app at: **http://sword.test**

Valet automatically serves the app on port 80 (no port needed).

#### Option 2: With Specific Port (sword.test:8000)
If you specifically need port 8000:
```bash
php artisan serve --host=sword.test --port=8000
```
Then access at: **http://sword.test:8000**

> **Note:** Valet typically serves apps without needing a port number. Using `php artisan serve` is only needed if you have a specific reason to use port 8000.

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Run tests
php artisan test

# Run Tinker (Laravel REPL)
php artisan tinker

# View all routes
php artisan route:list
```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Feature Ideas

- Fill in author & description for each book
- Look for similiarities between stories
- Build themes with supporting verses such as:
    - the word
    - patience
    - fruits of the spirit, etc.
- Memory verses, track for user
- History of how it was put together
- Personal commentary, save for user
- Timeline of books written
- Look into missing books, verses

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

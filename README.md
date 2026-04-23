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

## Feature Ideas

- Look for similiarities between stories, new page for that
- History of how a translation/book was put together
- Timeline of books written
- Look into missing books, verses
- fix the search in navbar to search verses
   - allow search on mobile
- set up notifications
- keep track of streaks?
- add swal() instead of alerts
- topic show make text fit better on mobile
- tablet size, allow compare
- make memory goals editable
- create topic
   - make cancel go back to index
   - handle validation with swal()
   - remove "last entry" 
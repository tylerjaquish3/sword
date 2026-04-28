# Sword

Sword is a personal Bible study application built with Laravel. It lets you read scripture across multiple translations, build a commentary layer of your own notes and reflections, maintain a structured prayer journal, track memory verse goals, and organize topical studies — all stored locally in a single SQLite file.

## Core Features

- **Multi-translation Bible reader** — Read KJV, NIV, NLT, and other translations side by side. Your last-read position is remembered per translation, and you can mark chapters as read to track progress.
- **Commentary** — Attach personal notes to individual verses or entire chapters. Browse all your commentary in a searchable index.
- **Prayer journal** — Log prayers by date and category (Adoration, Confession, Thanksgiving, Supplication). Entries are grouped by date and filterable by type.
- **Scripture memory** — Create memory goals with a set of verses, a start date, and an optional deadline. Mark goals complete when finished.
- **Topic studies** — Create topics with keywords, search the full Bible text for relevant verses, and attach notes to individual verses within each topic.
- **Full-text search** — Search across all Bible translations simultaneously by keyword or phrase.
- **Dashboard** — See reading statistics, recent activity (last 7 days), and a snapshot of your study patterns.
- **Profile & history** — View your complete reading history, login history, and all commentary in one place.

## Tech Stack

- **Backend:** Laravel 10, PHP 8.1+
- **Database:** SQLite (`database/database.sqlite`)
- **Frontend:** Blade templates, Bootstrap 5, jQuery 4, DataTables, Chart.js, Moment.js
- **Build tool:** Vite
- **Local serving:** Laravel Valet (`http://sword.test`)
- **Bible data:** Fetched from the Keplin API and stored locally

---

## Feature Ideas

- Look for similiarities between stories, new page for that
- History of how a translation/book was put together
- Timeline of books written
- Look into missing books, verses
- set up notifications
- tablet size, allow compare
- collaboration tools, accountability partner
- import ESV translation (see details below)
- d3 word cloud for chapter
- look at other bible apps and what features they have
- accountability worksheet like what Aaron sent me
- dashboard overview counts verses for each txn instead of total
- dashboard confession has no icon

# locally — run as many times as needed until complete
php artisan esv:import
php artisan esv:import --limit=100

# once all 1,189 chapters are done
php artisan esv:export-migration
git add database/migrations/YYYY_MM_DD_HHMMSS_seed_esv_verses.php
git commit -m "Add ESV verse seed migration"
git push
Then on prod:
php artisan migrate


also need to run the key words job:
php artisan keyWords

## Claude ideas

- Verse Connection Graph — Render the existing verse_links table as an interactive D3.js network. Click a verse, see a web of cross-references radiating outward, color-coded by book.

- Personal Concordance / Word Cloud — Aggregate key_words from all verses you've commented on, highlighted, or added to topics. Clicking a word shows all your study notes for verses with that keyword.

- Verse Highlighter — Color-coded verse annotations in the reading view: Yellow (important), Blue (prophecy), Green (promise), Red (command). Stored in a verse_highlights table. Your own "illuminated Bible."

- Prophecy & Fulfillment Tracker — A specialized version of verse links: tag OT verses as prophecies and link them to NT fulfillments. A dedicated browser shows pairs side-by-side. Just needs a link_type column on verse_links.

- Scripture Memory Flashcard Quiz — Turn memory goals into interactive quizzes. Show the reference, user types the verse, reveal the text, mark Got It / Try Again. Track mastery percentage per verse over time.

- Reading Plan Builder — Create structured plans ("NT in 90 days," custom). Daily assignments surface on the dashboard. Today's reading shows "Day 14 of 90" in the chapter view.

---

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

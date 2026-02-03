# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Sword is a Laravel-based Bible study application that allows users to read multiple Bible translations, add personal commentary and prayers, organize topics, and track memory verses. The application fetches Bible text from the Keplin API and stores it locally in SQLite.

## Tech Stack

- **Backend**: Laravel 10, PHP 8.1+
- **Database**: SQLite (single file at `database/database.sqlite`)
- **Frontend**: Blade templates, Bootstrap 5, jQuery 4, DataTables, Chart.js, Moment.js
- **Build Tool**: Vite
- **Development**: Laravel Valet for local serving
- **External API**: Keplin API for fetching Bible verses

## Common Commands

### Development
```bash
# Start Valet server (access at http://sword.test)
valet link sword

# Build frontend assets
npm run dev          # Development with hot reload
npm run build        # Production build

# Clear all Laravel caches
php artisan optimize:clear

# View all routes
php artisan route:list

# Laravel REPL
php artisan tinker
```

### Database
```bash
# Run migrations
php artisan migrate

# Create fresh database
touch database/database.sqlite
php artisan migrate:fresh
```

### Testing
```bash
# Run PHPUnit tests
php artisan test

# Run specific test file
php artisan test --filter=TestName
```

### Code Quality
```bash
# Run Laravel Pint (code formatter)
./vendor/bin/pint

# Format specific files
./vendor/bin/pint app/Http/Controllers
```

### Background Jobs
```bash
# Import Bible verses from Keplin API (sync)
php artisan tinker
>>> App\Jobs\KeplinVerses::dispatchSync();

# Process keywords from verses
>>> App\Jobs\DetermineKeyWords::dispatchSync();
```

## Architecture

### Database Structure

The application uses a hierarchical Bible structure:

**Core Bible Data**:
- `books` → `chapters` → `verses` (many-to-many with translations)
- `translations` (KJV, NIV, NLT, etc.)
- Each verse belongs to a chapter and a translation

**User Content**:
- `verse_comments` - Commentary on specific verses (stores `chapter_id` + `verse_number`)
- `chapter_comments` - Commentary on entire chapters
- `prayers` - User prayers with types (`prayer_types`)
- `topics` - User-defined topics with associated verses
- `verse_links` - Cross-references between verses

**Key Relationships**:
- Books have many Chapters
- Chapters have many Verses (per translation)
- Verses belong to a Translation
- Comments link to chapters/verses for user annotations

### Controllers & Routes

Controllers follow resource patterns where applicable:

- `HomeController` - Dashboard with statistics and recent activity
- `ChapterController` - Chapter lookup and comment retrieval
- `TranslationController` - CRUD for translations, verse viewing/editing
- `CommentaryController` - Manages both verse and chapter comments
- `PrayerController` - Resource controller for prayers
- `TopicController` - Resource controller for topics

Routes are defined in [routes/web.php](routes/web.php) with route groups for:
- `/chapters/*` - Chapter operations
- `/commentary/*` - Commentary CRUD with separate routes for verse/chapter comments
- `/prayers` - Resource routes
- `/topics` - Resource routes
- `/translations/*` - Translation and verse operations

### Background Jobs

Located in [app/Jobs/](app/Jobs/):

- `KeplinVerses` - Fetches Bible text from Keplin API for specified books and translations
  - Requires `KEPLIN_API_HOST` environment variable
  - Handles rate limiting and API errors
  - Creates Books, Chapters, Verses, and Translations
  - Can be run for specific Bible versions (KJV, NIV, NLT)

- `FetchVerses` - Alternative verse fetching job

- `DetermineKeyWords` - Processes verses to extract meaningful keywords
  - Filters out common words using `App\Enum\CommonWords`
  - Used for search and topic extraction

### Frontend Architecture

Views are organized by feature in [resources/views/](resources/views/):

- `base/` - Shared layouts (navbar, footer)
- `home/` - Dashboard
- `commentary/` - Commentary interface with modals
- `prayers/` - Prayer management with partials
- `topics/` - Topic management with partials
- `translations/` - Translation and verse views with partials
- `verses/` - Verse display components
- `memory/` - Memory verse tracking (new feature)

**JavaScript Libraries** (loaded via Vite):
- jQuery 4 for DOM manipulation
- DataTables for tabular data
- Chart.js with datalabels plugin for statistics
- Bootstrap 5 for UI components
- Moment.js for date formatting

### Environment Configuration

Beyond standard Laravel config, this application requires:

```bash
# Keplin Bible API configuration
KEPLIN_API_HOST=https://api.example.com
KEPLIN_API_EMAIL=your@email.com  # If authentication is enabled
```

The API client in `KeplinVerses` job expects JSON responses with book, chapter, and verse data.

### Models

All models use `protected $guarded = []` for mass assignment flexibility:

- `Book` - Bible books (Old/New Testament flag)
- `Chapter` - Chapters within books
- `Verse` - Individual verses with reference and text
- `Translation` - Bible translations (KJV, NIV, etc.)
- `VerseComment` - User commentary on verses (has `scopeForVerse` helper)
- `ChapterComment` - User commentary on chapters
- `Prayer` - User prayers (belongs to PrayerType)
- `Topic` - User-defined topics
- `VerseLink` - Cross-reference links between verses

## Development Notes

- This is a **single-user application** (no authentication gates on routes)
- SQLite is used for simplicity; all data in one file
- Valet serves the app without needing `php artisan serve`
- Frontend assets are compiled with Vite (replaces Laravel Mix)
- Verse text is indexed for full-text search capabilities
- The app tracks user study patterns, commentary, and prayer history

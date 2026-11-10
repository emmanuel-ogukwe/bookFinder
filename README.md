# 📚 Universal Free & Accessible Book Finder

**Universal Book Finder** is an open-source web application designed to help readers navigate the fragmented world of digital literature.

Instead of hosting copyrighted files, this project serves as a **centralized aggregator and search engine**. Users can search for a specific book title and instantly discover the legal avenues available to read it—whether that means downloading a public domain EPUB, borrowing from a local library via API, or reading a preview.

Originally built with **React + FastAPI**, this project has been converted to a single **Laravel** application (Blade + vanilla JS frontend, Laravel API backend).

## 🎯 The Mission

The internet is full of information, but finding a specific book legally can be a nightmare. We aim to solve the "Where can I read this?" problem by checking multiple legitimate sources simultaneously.

**We do not host or store book PDFs.** We simply connect readers to the platforms that do.

## ✨ How It Works

1. **Search:** A user enters a book title or author.
2. **Aggregate:** The system queries multiple supported APIs in parallel (Open Library, Project Gutenberg, Internet Archive, Google Books).
3. **Filter:** Results are categorized by access type (Public Domain, Borrowable, Preview, Purchasable).
4. **Redirect:** The user is sent to the source to consume the content legally.

## 🛠️ Tech Stack

- **Backend:** Laravel (PHP) — HTTP pooling via Laravel's HTTP client for parallel source queries.
- **Frontend:** Blade + vanilla JavaScript + CSS (no build step).
- **APIs:** Open Library API, Project Gutenberg (Gutendex), Internet Archive, Google Books API.

## 📦 Getting Started

### Prerequisites

- PHP 8.2+ with `curl`, `mbstring`, `pdo_sqlite`/`sqlite3` extensions
- Composer

### Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

The app will be available at `http://localhost:8000`.

### Tests

```sh
php artisan test
```

### Project structure

```
app/
  Http/Controllers/BookSearchController.php   # /api/search endpoint
  Services/                                   # one service per source API
public/
  css/app.css                                  # styles (light/dark themes)
  js/app.js                                    # search/filter UI behavior
resources/views/search.blade.php               # main page
routes/
  web.php                                      # GET /   -> search page
  api.php                                      # GET /api/search
```

## 🤝 Contributing

We welcome contributions from developers, designers, and book lovers! Whether you want to add a new source API or improve the UI, please check out our `CONTRIBUTING.md`.

## ⚖️ Legal Disclaimer

This project is a search aggregator. It does not host, upload, or distribute copyrighted material. All links provided direct users to legitimate third-party services (e.g., library systems, public domain archives, or official retailer previews). We strictly oppose digital piracy.
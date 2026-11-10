<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
</head>
<body>
    <div class="container">
        <form class="searchBar" id="searchForm">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input
                id="searchInput"
                type="text"
                placeholder="Search for a book..."
                autocomplete="off"
            />
            <button type="submit" class="searchBtn" id="searchBtn" disabled>Search</button>
        </form>

        <p class="resultCount" id="resultCount" hidden></p>

        <div class="loader" id="loader" hidden>
            <span class="spinner"></span>
            Searching across sources...
        </div>

        <div class="empty" id="empty" hidden>
            No results found. Try a different search.
        </div>

        <div class="resultsList" id="resultsList"></div>

        <button class="fab" id="filterFab">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
            </svg>
            Filters
        </button>

        <div class="filterPanel" id="filterPanel" hidden>
            <div class="filterPanelHeader">
                Filter by access type
                <button class="filterClose" id="filterClose">&times;</button>
            </div>
            <div class="filterOptions" id="filterOptions"></div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
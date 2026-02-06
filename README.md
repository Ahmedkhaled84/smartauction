# SmartAuction

A lightweight Laravel app to manage sellers, inventory items, and live auction rows. It includes a command-style home page, sellers management, and a smart auction table with quick inline edits.

## What This Project Does

### 1) Home Page (Command Window)
- Shows a terminal-like UI.
- Commands:
  - `new auction` → choose start/end date, then create a new auction code.
  - `load <code>` → load an existing auction session.
  - `show codes` → list all auction codes.
- Current auction code is shown in the navbar.

### 2) Sellers Page (Section 1)
- Create and edit sellers with:
  - Name
  - Number (unique)
  - Phone
  - ID number (unique, optional)
  - Items and quantities
  - Saved date is automatic (today)
- Each seller is tied to the current auction session.
- Export sellers to CSV (per current auction).

### 3) Smart Auction Table
- Full-width table (Excel-like style).
- Add a new row from the last row button (no separate form).
- Inline edit for each row (price and buyer in-place).
- Each auction row is tied to the current auction session.
- Export auction table to CSV (per current auction).

## Auction Sessions
- Auctions have:
  - Code
  - Start date
  - End date
- Auction code format:
  - `startDay.endDay-month-yy`
  - Example: start 10, end 12, month 2, year 2026 → `10.12-2-26`
- When you create or load a code, Sellers and Table show only data from that auction.

## How It Works (Short)
- Sellers and auction rows are filtered by the current auction session (stored in session).
- Existing data was migrated to a default auction code: `841999` with dates `2026-02-04` → `2026-02-06`.

## Run Locally

### 1) Install
```
composer install
npm install
```

### 2) Migrate
```
php artisan migrate
```

### 3) Build assets (recommended for mobile/production look)
```
npm run build
```

### 4) Start server
```
php artisan serve --host 0.0.0.0 --port 8000
```

Open:
```
http://<YOUR_IP>:8000
```

## Notes / Known Behaviors
- If you see the default Vite page on `:5173`, that is normal. Use `:8000` for the app.
- If CSS does not appear on mobile, make sure you ran `npm run build` and `public/hot` is removed.

## TODO / Next Features (Planned)
- Add more terminal commands.
- Add validation rules for auction dates (optional).
- Add per-auction reports and analytics.
- Add roles/permissions for admins vs operators.
- Improve error messages in terminal UI.

## Data Model Summary
- `auctions`: code, start_date, end_date
- `subjects`: seller info + items (linked to auction)
- `auction_entries`: auction table rows (linked to auction)

---

If you want the README in Arabic or want to expand a specific section, tell me what you want to add.
# smartauction

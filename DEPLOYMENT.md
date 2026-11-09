# DonutsTecLiberia — Deployment Guide

## 1. Shared Hosting (cPanel)

### Step 1 — Upload files
- Zip the entire `donutstec/` folder
- Upload to `public_html/` via cPanel File Manager
- Extract

### Step 2 — Create the database
- cPanel → MySQL Databases
- Create DB: `donutstec_db`
- Create user + grant ALL
- phpMyAdmin → Import `database/schema.sql` → then run the Phase 5, 6, 7 SQL patches

### Step 3 — Update config
Edit `config/config.php`:
```php
define('DTC_ENV', 'production');
define('BASE_URL', 'https://yourdomain.com');
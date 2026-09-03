# Webstep Dynamic Packages — PHP Backend

## Folder Structure

```
webstep-backend/
├── config/
│   └── db.php                  ← DB credentials (edit this)
├── includes/
│   └── helpers.php             ← CORS, response, auth helpers
├── api/                        ← PUBLIC endpoints (no auth)
│   ├── packages.php            ← GET packages + tabs
│   ├── custom-options.php      ← GET custom builder options
│   ├── submit-custom.php       ← POST custom package request
│   └── submit-inquiry.php      ← POST "Choose Plan" inquiry
├── admin/                      ← PROTECTED endpoints + panel
│   ├── auth.php                ← POST login / GET logout
│   ├── packages.php            ← CRUD packages
│   ├── tabs.php                ← CRUD category tabs
│   ├── custom-options.php      ← CRUD builder options
│   ├── submissions.php         ← View/manage leads
│   └── index.html              ← Admin panel UI
├── setup.sql                   ← DB setup + seed data
├── contentApi.js               ← Replace lib/contentApi.js
└── CustomizePackage.jsx        ← Updated component (dynamic options)
```

---

## Setup (5 steps)

### 1. Create the database and run migrations

```bash
mysql -u root -p
# Then in MySQL:
source /path/to/webstep-backend/setup.sql
```

Or paste the contents of `setup.sql` into phpMyAdmin.

### 2. Place backend in your web server root

Copy the `webstep-backend/` folder to:
- XAMPP: `C:/xampp/htdocs/webstep-backend/`
- WAMP:  `C:/wamp64/www/webstep-backend/`
- Linux: `/var/www/html/webstep-backend/`

### 3. Update DB credentials

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'yourpassword');
define('DB_NAME', 'webstep');
```

### 4. Update frontend API URL

In your Next.js project, add to `.env.local`:
```
NEXT_PUBLIC_API_URL=http://localhost/webstepphp
```

### 5. Replace frontend files

| Replace this file               | With file from backend folder   |
|---------------------------------|---------------------------------|
| `lib/contentApi.js`             | `contentApi.js`                 |
| `components/CustomizePackage.jsx` | `CustomizePackage.jsx`        |

The `Packages.jsx` component already calls `fetchContent('packages', fallback)` —
no changes needed there, it auto-fetches from the PHP API.

---

## Admin Panel

Open in browser:
```
http://localhost/webstepphp/admin/index.html
```

**Default credentials:** `admin` / `admin123`

**Change password** (run in MySQL):
```sql
UPDATE admin_users
SET password = '$2y$10$YOUR_BCRYPT_HASH_HERE'
WHERE username = 'admin';
```

Generate a bcrypt hash: https://bcrypt-generator.com/

---

## API Endpoints Reference

### Public (no auth needed)

| Method | Endpoint                      | Description                        |
|--------|-------------------------------|------------------------------------|
| GET    | `/api/packages.php`           | Get all tabs + packages for frontend |
| GET    | `/api/custom-options.php`     | Get custom builder options         |
| POST   | `/api/submit-custom.php`      | Submit custom package request      |
| POST   | `/api/submit-inquiry.php`     | Submit "Choose Plan" inquiry       |

### Admin (login required via `/admin/auth.php`)

| Method | Endpoint                          | Description              |
|--------|-----------------------------------|--------------------------|
| POST   | `/admin/auth.php?action=login`    | Login                    |
| GET    | `/admin/auth.php?action=logout`   | Logout                   |
| GET/POST/PUT/DELETE | `/admin/packages.php` | Package CRUD    |
| GET/POST/PUT/DELETE | `/admin/tabs.php`     | Tab CRUD        |
| GET/POST/PUT/DELETE | `/admin/custom-options.php` | Builder options CRUD |
| GET/PUT/DELETE | `/admin/submissions.php?type=custom\|inquiry` | Manage leads |

---

## What's Dynamic Now

✅ **Package tabs** — add/remove/reorder categories from admin  
✅ **Package cards** — title, price, features, popular badge — all editable  
✅ **Custom builder options** — website types, pages, design levels, features  
✅ **All prices** — change any price from admin, frontend updates instantly  
✅ **Custom submissions** — saved to DB with full package breakdown  
✅ **Plan inquiries** — saved to DB with lead management  
✅ **Status tracking** — new → viewed → contacted → closed  

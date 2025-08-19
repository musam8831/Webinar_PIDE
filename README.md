# Webinar Booking App

Features
- PHP + MySQL
- User auth (login/logout)
- Admin user CRUD (create, read, update, delete)
- Webinar booking with conflict prevention (create & update both enforce no overlap)
- Only initiator or admin can delete
- Tooltip on hover (no blinking; uses Tippy.js)
- Popup modal to add/edit webinar (not prompts)
- **Admin-only Yearly Dashboard** (single screen with a switcher):
  1) Heatmap-style grid
  2) Classic mini-calendar (12 months)
  3) Weekly Grid (Mon–Fri × 52 weeks)

## Setup
1. Create a MySQL DB, update credentials in `includes/config.php`.
2. Import `schema.sql` (or run `setup.php` once).
3. Point web server to `public/` as document root.
4. Login with default admin: `admin@example.com` / `Admin@123`

## Routes
- `/public/login.php`
- `/public/index.php` — main calendar
- `/admin/users.php` — user CRUD (admin only)
- `/public/yearly_dashboard.php` — admin dashboard (3 views)

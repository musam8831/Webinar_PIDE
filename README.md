# Webinar Booking App — Full Build

Features
- PHP + MySQL
- User auth (login/logout)
- Admin user CRUD (create, read, update, delete)
- Webinar booking with conflict prevention
- Only initiator or admin can delete
- Hover/tooltip shows webinar info
- **Popup modal** to add/edit webinar (no prompts)
- **Admin-only Yearly Dashboard** with 3 switchable layouts on one screen:
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

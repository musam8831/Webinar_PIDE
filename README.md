
# Webinar Booking App (with Google Login)
- PHP + MySQL
- Password login + Google OAuth2 (no composer needed)
- Admin user CRUD
- Webinar booking with conflict prevention
- Admin-only Yearly Dashboard with 3 layouts (Heatmap, Mini-Calendars, Weekly Grid)

## Quick Start
1) Create a MySQL DB and import `webinar_app.sql`.
2) Edit `includes/config.php` with your DB credentials and Google OAuth keys.
3) Set your web server document root to `public/`.
4) Visit `/public/login.php` and sign in:
   - Email/password (default admin: admin@example.com / Admin@123)
   - or Google

## Google OAuth Setup
- Create OAuth 2.0 Client ID at https://console.cloud.google.com/apis/credentials
- Authorized redirect URI: `http://localhost/webinar_app/public/google_callback.php`
- Put Client ID/Secret in `includes/config.php`.


# PIDE Webinar Portal - Enhanced Edition

A comprehensive webinar scheduling and management application for the Pakistan Institute of Development Economics (PIDE), featuring webinar approval workflows, categorization, and detailed reporting.

## Key Features

### Core Functionality
- **Interactive Calendar Interface**: FullCalendar 6 integration for scheduling
- **Category Management**: Organize webinars by custom categories
- **Two-Tier Approval Workflow**: Admin review before webinar publication
- **Advanced Filtering**: Filter by user, date range, category, and search
- **Performance Analytics**: Comprehensive reports with charts
- **Yearly Dashboard**: Visual analytics (heatmap, mini calendars, weekly grid)
- **User Management**: Admin user creation and role management
- **Google OAuth Integration**: Secure authentication with Google accounts

### Recent Enhancements (v2.0)
✓ Webinar category system with CRUD operations  
✓ Admin approval workflow for submissions  
✓ Pending webinars dashboard for administrators  
✓ Advanced webinars list with multiple filters  
✓ Rejection reasons tracking  
✓ ROOT-based index.php access  
✓ BASE_URL configuration  
✓ Unit testing framework  
✓ Extended database schema  

## Quick Start (5 Minutes)

### 1. Database Setup
```bash
# Import updated schema
mysql -u root webinar_app < webinar_app_updated.sql
```

### 2. Configure Application
Edit `includes/config.php`:
```php
'base_url' => '/Webinar_PIDE/',
'db' => [
  'host' => '127.0.0.1',
  'name' => 'webinar_app',
  'user' => 'root',
  'pass' => ''  // Your password
]
```

### 3. Access Application
- **Calendar**: http://localhost/Webinar_PIDE/index.php
- **Login Credentials**:
  - Admin: admin@example.com / Admin@123
  - User: user@example.com / User@123

### 4. Run Tests
```bash
php tests/unit_tests.php
```

## Project Structure
```
Webinar_PIDE/
├── index.php                    # Main calendar (NEW: ROOT location)
├── includes/
│   ├── config.php              # Configuration with BASE_URL
│   ├── db.php                  # Database connection
│   └── auth.php                # Authentication helpers
├── public/
│   ├── navbar.php              # Navigation component
│   ├── webinars_list.php       # User/Admin webinars list (NEW)
│   ├── load_events.php         # API: Load approved webinars
│   ├── save_event.php          # API: Create webinar submission
│   ├── update_event.php        # API: Update (admin only)
│   ├── delete_event.php        # API: Delete
│   ├── yearly_dashboard.php    # Yearly analytics
│   ├── login.php               # Login interface
│   └── [other pages]
├── admin/
│   ├── categories.php          # Category management (NEW)
│   ├── pending_webinars.php    # Approve/reject submissions (NEW)
│   ├── reports.php             # Analytics dashboard
│   ├── users.php               # User management
│   └── [other pages]
├── assets/
│   ├── css/                    # Stylesheets
│   └── img/                    # Images
├── tests/
│   └── unit_tests.php          # Unit tests (NEW)
├── webinar_app.sql             # Original schema
├── webinar_app_updated.sql     # Updated schema (NEW)
└── README.md                   # This file
```

## Database Schema Updates

### Categories Table (NEW)
```sql
CREATE TABLE categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  is_active TINYINT DEFAULT 1,
  created_by INT NOT NULL,
  created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_by INT,
  modified_on TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
);
```

### Webinars Table Updates
```sql
ALTER TABLE webinars ADD COLUMN category_id INT;
ALTER TABLE webinars ADD COLUMN is_approved TINYINT DEFAULT 0;
ALTER TABLE webinars ADD COLUMN approved_by INT;
ALTER TABLE webinars ADD COLUMN approved_on TIMESTAMP NULL;
ALTER TABLE webinars ADD COLUMN rejection_reason TEXT;
ALTER TABLE webinars ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;
```

## User Guide

### For All Users

**View Calendar**
- Navigate to: http://localhost/Webinar_PIDE/index.php
- Shows only approved webinars
- Filter by week, day, month

**View My Webinars**
- Click "My Webinars" in navigation
- Filter by: Date, Category, Search text
- See: approval status, dates, categories

### For Regular Users

**Submit a Webinar**
1. Click calendar time slot
2. Fill form: Title, Category, Start, End
3. Click "Submit"
4. Wait for admin approval

**Edit/Delete Submissions**
- Can only edit/delete unapproved webinars
- After approval, only admin can modify

### For Administrators

**Approve Webinars**
- Click "Pending Approval" in navigation
- Review submissions
- Click "Approve" to publish or "Reject" with reason

**Manage Categories**
- Click "Categories" in navigation
- Add, Edit, Delete categories
- Set Active/Inactive status

**View Reports**
- Click "Reports"
- Select year
- View: User contributions, trends, statistics
- Export: Download as PDF

**Yearly Dashboard**
- Click "Yearly Dashboard"
- View: Heatmap, Mini Calendars, Weekly Grid
- Click dates to see webinar details

**Advanced Filtering**
- In "My Webinars": Filter by other users (admin only)
- Date ranges: Today, Week, Month, Year, Custom
- Search: Find by title/name

## Approval Workflow

```
User Submits Webinar → is_approved = 0
                ↓
Admin Reviews (Pending Approval)
        ↓
    ┌──┴──┐
    ↓     ↓
APPROVE REJECT
    ↓     ↓
 [1]   [Send Reason]
    ↓
Shows in:
- Calendar
- Reports
- Dashboard
- Lists
```

## API Endpoints

All require authentication.

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `public/load_events.php` | GET | Fetch approved webinars |
| `public/save_event.php` | POST | Submit new webinar |
| `public/update_event.php` | POST | Update webinar (admin) |
| `public/delete_event.php` | POST | Delete webinar |
| `public/get_event.php?id=X` | GET | Get event details |
| `public/fetch_year_events.php?year=Y` | GET | Yearly data |

## Testing

```bash
# Run all tests
php tests/unit_tests.php

# Tests verify:
✓ Database schema structure
✓ Configuration validity
✓ File presence
✓ Navigation items
✓ Foreign key constraints
```

## Configuration Options

Edit `includes/config.php`:

```php
return [
  'base_url' => '/Webinar_PIDE/',        // Deployment path
  'db' => [
    'host' => '127.0.0.1',               // DB hostname
    'port' => 3306,                      // DB port
    'name' => 'webinar_app',             // DB name
    'user' => 'root',                    // DB user
    'pass' => ''                         // DB password
  ],
  'google' => [
    'client_id' => '...',                // Google OAuth ID
    'client_secret' => '...',            // Google OAuth Secret
    'redirect_uri' => '...'              // OAuth redirect
  ]
];
```

## Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| Blank category dropdown | No active categories | Add via admin panel |
| Can't edit approved webinar | Permission denied | Use admin account |
| Webinar missing from calendar | Not approved yet | Check pending approvals |
| Database error | Wrong credentials | Update config.php |

## Security Notes

- All passwords: bcrypt hashing
- SQL: Prepared statements
- Input: Validated/sanitized
- Auth: Session-based with role checking
- Recommend: HTTPS, strong DB password, regular backups

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Dependencies

### Libraries
- Bootstrap 5.3.3 (UI)
- FullCalendar 6.1.15 (Calendar)
- Chart.js 4.4.1 (Reports)
- Tippy.js 6 (Tooltips)

### Server
- PHP 8.0+
- MySQL 10.4+ / MariaDB 10.4+
- PDO extension

## File Changes Summary

### New Files
- `index.php` (root)
- `admin/categories.php`
- `admin/pending_webinars.php`
- `public/webinars_list.php`
- `tests/unit_tests.php`
- `webinar_app_updated.sql`

### Modified Files
- `includes/config.php` (added BASE_URL)
- `public/navbar.php` (new menu items)
- `public/*.php` (approval logic)
- `admin/reports.php` (approved only)

## Version History

**v2.0** (Current)
- Approval workflow
- Categories system
- Advanced filters
- Unit tests

**v1.0**
- Basic calendar
- User CRUD
- Google OAuth
- Reports

## Support

For help:
1. Check README Troubleshooting
2. Run tests: `php tests/unit_tests.php`
3. Review logs
4. Verify DB connection

---

**Last Updated**: November 25, 2025  
**Version**: 2.0 - Production Ready


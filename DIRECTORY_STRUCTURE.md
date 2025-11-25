# Project Directory Structure - PIDE Webinar Portal v2.0

Complete directory and file listing with descriptions.

```
Webinar_PIDE/
│
├── index.php                          [NEW] Main calendar interface (ROOT)
│   └── Purpose: Displays webinar calendar, handles webinar submission
│   └── Access: Everyone (after login)
│   └── Category dropdown added
│
├── README.md                          [UPDATED] Comprehensive documentation (~500 lines)
├── QUICKSTART.md                      [NEW] 5-minute quick start guide
├── MIGRATION_GUIDE.md                 [NEW] Upgrade from v1.0 instructions
├── API_DOCUMENTATION.md               [NEW] Complete API reference for developers
├── CHANGELOG.md                       [NEW] Version history and changes
├── IMPLEMENTATION_SUMMARY.md          [NEW] Complete implementation overview
├── DELIVERABLES.md                    [NEW] Checklist of all deliverables
│
├── webinar_app.sql                    Original database schema (v1.0)
├── webinar_app_updated.sql            [NEW] Updated schema with categories & approval
│
├── includes/
│   ├── config.php                     [UPDATED] Configuration file
│   │   └── NEW: base_url key
│   │   └── Database connection settings
│   │   └── Google OAuth configuration
│   │
│   ├── db.php                         Database connection handler
│   │   └── PDO MySQL connection
│   │   └── Error mode configuration
│   │
│   └── auth.php                       Authentication functions
│       ├── require_login() - Enforce user login
│       └── require_admin() - Enforce admin role
│
├── public/
│   ├── index.php                      [DEPRECATED] Old calendar location (kept for reference)
│   │
│   ├── navbar.php                     [UPDATED] Navigation bar component
│   │   ├── NEW: Categories menu (admin)
│   │   ├── NEW: Pending Approval menu (admin)
│   │   ├── NEW: My Webinars menu (all users)
│   │   ├── Yearly Dashboard link (admin)
│   │   ├── Reports link (admin)
│   │   └── Logout link
│   │
│   ├── webinars_list.php              [NEW] Advanced webinars list with filters
│   │   ├── User filter (admin only)
│   │   ├── Date range filter
│   │   ├── Category filter
│   │   ├── Text search
│   │   └── Responsive grid layout
│   │
│   ├── login.php                      Login interface
│   │   ├── Email/password authentication
│   │   └── Google OAuth button
│   │
│   ├── logout.php                     Logout handler
│   │   └── Session cleanup
│   │
│   ├── google_login.php               Google OAuth initiation
│   │   └── Redirect to Google
│   │
│   ├── google_callback.php            Google OAuth callback handler
│   │   └── Process OAuth response
│   │
│   ├── load_events.php                [UPDATED] API: Load approved webinars
│   │   ├── Filter: is_approved=1 only
│   │   ├── Return: JSON array
│   │   ├── Include: category_title, initiator info
│   │   └── Permissions: Show can_edit, can_delete
│   │
│   ├── save_event.php                 [UPDATED] API: Create webinar submission
│   │   ├── Required: title, category_id, start, end
│   │   ├── Default: is_approved=0
│   │   ├── Validate: Active category exists
│   │   ├── Check: No overlaps with approved webinars
│   │   └── Return: {ok: true, id: X}
│   │
│   ├── get_event.php                  [UPDATED] API: Get event details
│   │   ├── Return: Event object with category_id
│   │   ├── Include: Local and UTC times
│   │   ├── Calculate: can_edit (admin only for approved)
│   │   └── Include: category_title
│   │
│   ├── update_event.php               [UPDATED] API: Update webinar (admin only)
│   │   ├── Permission: Admin only
│   │   ├── Update: title, category_id, start, end
│   │   ├── Validate: Active category
│   │   ├── Check: No overlaps with approved
│   │   └── Return: {ok: true}
│   │
│   ├── delete_event.php               [UPDATED] API: Delete webinar
│   │   ├── User: Can delete only own unapproved
│   │   ├── Admin: Can delete any
│   │   ├── Protect: Approved webinars
│   │   └── Return: {ok: true}
│   │
│   ├── fetch_year_events.php          [UPDATED] API: Get yearly statistics
│   │   ├── Filter: is_approved=1 only
│   │   ├── Query param: year
│   │   ├── Return: Events for dashboard/reports
│   │   └── Format: Array with date, start, end, initiator
│   │
│   ├── yearly_dashboard.php           [UPDATED] Yearly analytics dashboard
│   │   ├── Admin only
│   │   ├── Three view modes: Heatmap, Mini Calendars, Weekly Grid
│   │   ├── Show: Approved webinars only
│   │   ├── Interactivity: Click dates for details
│   │   └── Updated: Uses approved webinars only
│   │
│   └── [other files]                  Other existing public files
│       └── Unchanged and operational
│
├── admin/
│   ├── categories.php                 [NEW] Category management interface
│   │   ├── List view: All categories with status
│   │   ├── Add view: Create new categories
│   │   ├── Edit view: Modify categories
│   │   ├── Delete: With in-use protection
│   │   ├── Toggle: Active/Inactive status
│   │   └── Track: Created/Modified by user
│   │
│   ├── pending_webinars.php           [NEW] Webinar approval interface
│   │   ├── List: All unapproved webinars
│   │   ├── Filter: Pending submissions
│   │   ├── Actions: Approve, Reject
│   │   ├── Reject Modal: For rejection reason
│   │   ├── Display: Initiator, date, category, submission time
│   │   └── Real-time: Refresh after action
│   │
│   ├── reports.php                    [UPDATED] Analytics dashboard
│   │   ├── Year selector
│   │   ├── Bar chart: Webinars per user
│   │   ├── Pie chart: User contribution
│   │   ├── Line chart: Monthly trend
│   │   ├── Data table: Detailed statistics
│   │   ├── Filter: Approved webinars only
│   │   └── Export: PDF download
│   │
│   ├── users.php                      User management interface
│   │   ├── List users
│   │   ├── Add user
│   │   ├── Edit user
│   │   ├── Delete user
│   │   ├── Assign roles (admin/user)
│   │   └── Password management
│   │
│   └── [other admin files]            Other existing admin pages
│       └── Unchanged and operational
│
├── assets/
│   ├── css/
│   │   ├── styles.css                 Main stylesheet
│   │   │   ├── Layout styles
│   │   │   ├── Component styles
│   │   │   ├── Responsive design
│   │   │   └── Modal/form styles
│   │   │
│   │   └── PIDETheme.css              PIDE branding theme
│   │       ├── Color scheme
│   │       ├── Typography
│   │       ├── Brand guidelines
│   │       └── Custom components
│   │
│   └── img/
│       └── _PIDE LOGO White PNG.png   PIDE logo file
│           └── Used in navbar
│
├── tests/
│   └── unit_tests.php                 [NEW] Unit testing framework
│       ├── 10+ test cases
│       ├── Schema validation tests
│       ├── Configuration validation
│       ├── File presence checks
│       ├── Permission verification
│       ├── Database checks
│       ├── Colored CLI output
│       └── Automated test runner
│
└── [Git files]
    ├── .git/                          Git repository
    ├── .gitignore                     Git ignore rules
    └── [Other git config]
```

---

## File Statistics

### By Type
```
PHP Files:        25+
SQL Files:        2
Documentation:    6
CSS Files:        2
Image Files:      1
Test Files:       1
─────────────────────
TOTAL FILES:      37+
```

### By Category

#### New Files (9)
- 1 × PHP (index.php root)
- 3 × Admin PHP (categories.php, pending_webinars.php, etc.)
- 1 × Public PHP (webinars_list.php)
- 1 × Test PHP (unit_tests.php)
- 1 × SQL (webinar_app_updated.sql)
- 6 × Documentation (MD files)

#### Modified Files (10)
- 1 × Configuration (config.php)
- 9 × PHP files (navbar, APIs, etc.)
- 1 × Documentation (README.md)

#### Unchanged Files (15+)
- All utility files
- All existing public pages
- All existing admin pages
- CSS/Image files
- Database original schema

---

## Directory Access Levels

### Public Access (After Login)
```
/index.php                          ✓ Calendar
/public/webinars_list.php          ✓ My Webinars
/public/yearly_dashboard.php       ✓ Dashboard (admin info)
/public/login.php                  ✓ Login
/public/logout.php                 ✓ Logout
```

### Admin Access Only
```
/admin/categories.php              ✓ Category Management
/admin/pending_webinars.php        ✓ Approval Dashboard
/admin/users.php                   ✓ User Management
/admin/reports.php                 ✓ Reports
```

### API Endpoints (Internal)
```
/public/load_events.php            ✓ Load webinars (auth required)
/public/save_event.php             ✓ Create webinar (auth required)
/public/get_event.php              ✓ Get details (auth required)
/public/update_event.php           ✓ Update (admin required)
/public/delete_event.php           ✓ Delete (auth required)
/public/fetch_year_events.php      ✓ Year data (admin required)
```

---

## Size Breakdown

### Code Files
- Core PHP: ~3,000+ lines
- Tests: ~400 lines
- Database Schema: ~250 lines

### Documentation
- README.md: ~500 lines
- QUICKSTART.md: ~250 lines
- MIGRATION_GUIDE.md: ~400 lines
- API_DOCUMENTATION.md: ~600 lines
- CHANGELOG.md: ~300 lines
- IMPLEMENTATION_SUMMARY.md: ~400 lines
- DELIVERABLES.md: ~350 lines
- **Total Docs: ~2,800+ lines**

---

## Configuration Files

### Application Config
- `includes/config.php` - Main configuration
  - Database credentials
  - Google OAuth keys
  - Base URL (NEW)

### Server Config
- `.htaccess` (if needed)
- `php.ini` settings (recommended)

### Database Config
- `webinar_app_updated.sql` - Schema with migrations
- `webinar_app.sql` - Original schema (backup)

---

## External Dependencies

### JavaScript Libraries
- Bootstrap 5.3.3 (CDN)
- FullCalendar 6.1.15 (CDN)
- Chart.js 4.4.1 (CDN)
- Tippy.js 6 (CDN)
- html2canvas 1.4.1 (CDN)
- jsPDF 2.5.1 (CDN)

### PHP Dependencies
- None (standard PHP functions only)
- Native PDO MySQL driver required

### System Requirements
- PHP 8.0+
- MySQL 10.4+
- Apache/Nginx

---

## Development Notes

### Code Organization
- Modular design with clear separation of concerns
- RESTful API endpoints
- Session-based authentication
- Role-based authorization

### Naming Conventions
- Files: snake_case (save_event.php)
- Functions: camelCase (requireLogin)
- Variables: camelCase ($userId)
- Constants: UPPER_CASE (DB_HOST)

### Best Practices
- Prepared statements for SQL
- Input validation and sanitization
- HTML escaping for output
- Proper error handling
- Consistent indentation (2-4 spaces)

---

## Backup Recommendations

### Before Deployment
1. Backup entire directory: `cp -r Webinar_PIDE Webinar_PIDE.backup`
2. Backup database: `mysqldump -u root webinar_app > backup.sql`
3. Document current version

### During Development
1. Use version control (Git)
2. Commit frequently
3. Branch for features
4. Keep remote backup

### Regular Maintenance
1. Weekly database backups
2. Monthly full backups
3. Off-site backup copies
4. Backup rotation strategy

---

## Quick Navigation

| Want to... | Go to... |
|-----------|----------|
| See calendar | /index.php |
| Manage categories | /admin/categories.php |
| Approve webinars | /admin/pending_webinars.php |
| View my webinars | /public/webinars_list.php |
| Check reports | /admin/reports.php |
| View dashboard | /public/yearly_dashboard.php |
| Check documentation | README.md |
| Setup quickly | QUICKSTART.md |
| Upgrade from v1 | MIGRATION_GUIDE.md |
| API reference | API_DOCUMENTATION.md |
| Run tests | tests/unit_tests.php |

---

## Implementation Timeline

Created: November 25, 2025  
Status: Production Ready  
Version: 2.0

---

**End of Directory Structure Documentation**

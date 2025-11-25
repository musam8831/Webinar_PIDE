# Changelog - PIDE Webinar Portal

All notable changes to the PIDE Webinar Portal are documented in this file.

## [2.0] - 2025-11-25

### Added

#### Core Features
- **Webinar Approval Workflow**
  - New `is_approved` field (default: 0) on webinars table
  - Only approved webinars visible on calendar and reports
  - Admin approval dashboard at `/admin/pending_webinars.php`
  - Ability to reject webinars with reasons

- **Category Management System**
  - New `categories` table with full CRUD operations
  - Admin interface at `/admin/categories.php`
  - Category dropdown in webinar submission form
  - Active/inactive category toggling
  - Foreign key constraint between webinars and categories

- **Enhanced Webinars List** (`/public/webinars_list.php`)
  - View webinars with advanced filtering
  - Filter by user (admin only)
  - Filter by date range (Today, Week, Month, Year, Custom)
  - Filter by category
  - Full-text search on title and name
  - Responsive grid layout

- **Database Enhancements**
  - `categories` table with id, title, description, is_active, created_by, created_on, modified_by, modified_on
  - New webinars columns: category_id, is_approved, approved_by, approved_on, rejection_reason
  - Foreign key constraints and proper indexing
  - Default categories pre-populated

#### File Structure Changes
- **Root-level index.php** - Moved calendar from `/public/index.php` to `/index.php`
  - Direct access at `http://localhost/Webinar_PIDE/index.php`
  - Updated all relative paths for root-level access

- **Configuration Enhancement**
  - Added `base_url` key to `includes/config.php`
  - Enables flexible deployment paths
  - Default value: `/Webinar_PIDE/`

#### User Interface Updates
- **Navigation Bar** (`public/navbar.php`)
  - New "Categories" menu item (admin only)
  - New "Pending Approval" menu item with notification badge (admin only)
  - New "My Webinars" menu item (all users)
  - Reorganized admin menu structure

- **Calendar Modal** (`index.php`)
  - Category dropdown field (required)
  - Informational message about approval workflow
  - Updated success message for submissions

#### API Endpoints (Updated)
- `public/save_event.php`: Added category_id validation, approval workflow integration
- `public/load_events.php`: Filter to approved webinars only, include category_title
- `public/get_event.php`: Return category_id, update edit permissions
- `public/update_event.php`: Restrict to admin only, validate category_id
- `public/delete_event.php`: Restrict deletion of approved webinars
- `public/fetch_year_events.php`: Show only approved webinars

#### Testing & Quality Assurance
- **Unit Testing Framework** (`tests/unit_tests.php`)
  - 10+ comprehensive test cases
  - Schema validation tests
  - Configuration validation
  - File presence checks
  - Permission verification
  - Database constraint checks
  - Automated test runner with colored output

#### Documentation
- **README.md** (Comprehensive, ~500 lines)
  - Feature overview
  - Installation & quick start (5 minutes)
  - User guide (regular users & admins)
  - Database schema documentation
  - API reference
  - Troubleshooting guide
  - Security considerations
  - Browser compatibility

- **QUICKSTART.md** (NEW)
  - 5-minute setup guide
  - Key features overview
  - First-time actions
  - Navigation reference
  - Common tasks
  - Default test accounts
  - Testing instructions

- **MIGRATION_GUIDE.md** (NEW)
  - Step-by-step upgrade from v1.0
  - Database migration options
  - File structure changes
  - Configuration updates
  - Testing procedures
  - Rollback procedures
  - Troubleshooting migration issues
  - Post-migration checklist

- **API_DOCUMENTATION.md** (NEW)
  - Complete endpoint reference
  - Request/response formats
  - Status codes
  - Integration examples
  - Database query examples
  - Rate limiting notes
  - Caching strategies
  - Security guidelines

- **IMPLEMENTATION_SUMMARY.md** (NEW)
  - Complete implementation overview
  - Feature checklist
  - File changes summary
  - Deployment checklist
  - Future enhancements

- **CHANGELOG.md** (NEW)
  - Version history and changes

### Changed

#### Database
- Updated `webinars` table with approval workflow fields
- Added indexes on `is_approved` column for performance
- Modified `reports.php` queries to show approved webinars only
- Modified `yearly_dashboard.php` queries to show approved webinars only

#### Backend Logic
- `public/save_event.php`: New webinars now require category and default to unapproved
- `public/load_events.php`: Now filters to approved webinars only
- `public/get_event.php`: Returns category_id and corrected edit permissions
- `public/update_event.php`: Restricted to admin users, validates category
- `public/delete_event.php`: Prevents deletion of approved webinars by non-admins
- `admin/reports.php`: Filters all data to approved webinars only
- `public/yearly_dashboard.php`: Uses approved webinars only in all views
- `admin/categories.php`: Updated delete protection and constraint handling

#### UI/UX
- `index.php`: Added category dropdown to webinar form
- `public/navbar.php`: Added new menu structure
- `admin/pending_webinars.php`: Enhanced approval interface with rejection modal

### Removed

#### Deprecated
- Legacy behavior of showing all webinars regardless of approval status
- Admin edit permission on user-created webinars (only own unapproved)

### Fixed

- Calendar display now only shows approved events
- Reports now show accurate data (approved events only)
- Overlap checking now considers only approved webinars
- User permission logic clarified (non-admins can't edit approved webinars)

### Security Enhancements

- Authorization checks strengthened on edit/delete operations
- Admin-only endpoints verify role before processing
- Input validation enhanced for category_id field
- SQL queries optimized with prepared statements for approval queries
- Foreign key constraints enforce data integrity

### Performance Improvements

- Added index on `webinars.is_approved` for faster filtering
- Optimized queries in `load_events.php` with explicit column selection
- Reduced query complexity in reports with filtered queries
- Better caching opportunities with smaller result sets

### Deprecated

- Direct access to `/public/index.php` (use `/index.php` instead)
- Old configuration structure without BASE_URL (new format backward compatible)

### Breaking Changes

- **Database Schema**: Existing installations must run migration
- **Entry Point**: Calendar moved from `public/index.php` to `index.php`
- **Webinar Visibility**: All webinars created in v2.0 require approval before appearing
- **API Behavior**: `load_events.php` now returns only approved webinars

### Notes for Upgrading

- Run `webinar_app_updated.sql` for schema changes
- Update configuration with BASE_URL
- Migrate existing webinars: `UPDATE webinars SET is_approved=1 WHERE is_approved=0`
- Update bookmarks to use new index.php location
- See MIGRATION_GUIDE.md for detailed upgrade instructions

---

## [1.0] - 2025-08-19

### Added (Original Features)

#### Core Functionality
- Interactive calendar interface with FullCalendar 6
- Webinar booking system with conflict prevention
- User authentication (password + Google OAuth 2.0)
- Two-tier role system (Admin, User)
- Admin user management interface

#### Features
- Webinar CRUD operations
- Admin-only yearly dashboard with 3 visualization options:
  - Heatmap view
  - Mini calendars view
  - Weekly grid view
- Analytics and reporting for admins
- Conflict detection and prevention
- Responsive Bootstrap 5 UI

#### Technologies
- PHP 8.0+
- MySQL/MariaDB 10.4+
- Bootstrap 5.3.3
- FullCalendar 6.1.15
- Google OAuth 2.0
- Chart.js 4.4.1
- Tippy.js 6 (tooltips)

#### Initial Database Schema
- `users` table with roles
- `webinars` table with basic fields
- User account management
- Password hashing with bcrypt

### Known Limitations (v1.0)

- No approval workflow (all webinars visible immediately)
- No categorization system
- Limited filtering capabilities
- No rejection feedback mechanism
- All users can edit any webinar (role-based only)

---

## Roadmap

### Planned for v2.1
- [ ] Email notifications for approvals/rejections
- [ ] Bulk category operations
- [ ] Export filtered lists (PDF/Excel)
- [ ] Webinar statistics per category
- [ ] Custom workflow configuration

### Planned for v3.0
- [ ] Mobile application
- [ ] GraphQL API
- [ ] Advanced permissions system
- [ ] Recurring webinars
- [ ] Attendee registration & tracking
- [ ] WebRTC integration for live sessions

---

## Compatibility Matrix

| Component | v1.0 | v2.0 | Status |
|-----------|------|------|--------|
| PHP | 7.4+ | 8.0+ | Upgraded |
| MySQL | 10.3+ | 10.4+ | Upgraded |
| Bootstrap | 5.3.3 | 5.3.3 | Maintained |
| FullCalendar | 6.1.15 | 6.1.15 | Maintained |

---

## Support & Maintenance

### v1.0
- Status: Deprecated
- End of support: November 25, 2025
- Upgrade path: See MIGRATION_GUIDE.md

### v2.0
- Status: Current (Production Ready)
- Support: Active
- Release date: November 25, 2025

---

## Contributors

- PIDE Development Team

---

## Version Reference

- **Latest Version**: 2.0
- **Previous Version**: 1.0
- **Next Version**: 2.1 (planned)

---

**Last Updated**: November 25, 2025

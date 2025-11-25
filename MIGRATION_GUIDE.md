# Migration Guide - V1.0 to V2.0

Upgrade your PIDE Webinar Portal from version 1.0 to 2.0 with new features and improved workflow.

## What's New in V2.0

### Major Features
1. **Webinar Approval Workflow** - Admin reviews before publication
2. **Category System** - Organize webinars by categories
3. **Advanced Filters** - Filter webinars by multiple criteria
4. **My Webinars Page** - View submissions with status
5. **Rejection Reasons** - Feedback when webinars are rejected
6. **ROOT-based Access** - Access at /index.php instead of /public/index.php
7. **Unit Testing** - Automated testing framework
8. **BASE_URL Config** - Flexible deployment paths

### Database Changes
- New: `categories` table
- Updated: `webinars` table with approval fields

## Pre-Migration Checklist

- [ ] Backup your database: `mysqldump -u root webinar_app > backup_v1.sql`
- [ ] Backup your files: `cp -r /path/to/Webinar_PIDE /path/to/Webinar_PIDE.backup`
- [ ] Stop any running cron jobs
- [ ] Notify users of maintenance
- [ ] Test migration in development first

## Step-by-Step Migration

### Step 1: Backup Everything (5 mins)

```bash
# Backup database
mysqldump -u root webinar_app > backup_v1_$(date +%Y%m%d).sql

# Backup files
cp -r /var/www/Webinar_PIDE /var/www/Webinar_PIDE.backup_v1
```

### Step 2: Update Database Schema (5 mins)

**Option A: Full Import (Recommended for fresh installs)**
```bash
mysql -u root webinar_app < webinar_app_updated.sql
```

**Option B: Manual Migration (If you have custom data)**

First, add new tables:
```sql
-- Create categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_by` int(11),
  `modified_on` timestamp NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO categories (title, description, is_active, created_by) VALUES
('Technical', 'Technical webinars and workshops', 1, 1),
('Research', 'Research-focused webinars', 1, 1),
('Training', 'Training and development sessions', 1, 1),
('Awareness', 'Awareness and information sessions', 1, 1);
```

Then update webinars table:
```sql
-- Add new columns if they don't exist
ALTER TABLE webinars ADD COLUMN IF NOT EXISTS category_id INT;
ALTER TABLE webinars ADD COLUMN IF NOT EXISTS is_approved TINYINT DEFAULT 0;
ALTER TABLE webinars ADD COLUMN IF NOT EXISTS approved_by INT;
ALTER TABLE webinars ADD COLUMN IF NOT EXISTS approved_on TIMESTAMP NULL;
ALTER TABLE webinars ADD COLUMN IF NOT EXISTS rejection_reason TEXT;

-- Add constraints
ALTER TABLE webinars 
ADD CONSTRAINT fk_webinar_category 
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Add indexes
ALTER TABLE webinars ADD INDEX idx_approved (is_approved);

-- Mark existing webinars as approved (legacy data)
UPDATE webinars SET is_approved=1, approved_by=1, approved_on=NOW() WHERE is_approved=0;
```

### Step 3: Update Configuration (2 mins)

Edit `includes/config.php`:
```php
return [
  'base_url' => '/Webinar_PIDE/',  // Add this line
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'webinar_app',
    'user' => 'root',
    'pass' => 'your_password'  // Update if needed
  ],
  'google' => [
    // ... existing Google config ...
  ]
];
```

### Step 4: Update File Structure (5 mins)

The main entry point has moved from `public/index.php` to root `index.php`.

**Old Structure:**
```
public/index.php  ← Main calendar
public/navbar.php
public/login.php
```

**New Structure:**
```
index.php         ← Main calendar (NEW)
public/navbar.php
public/login.php
```

**Action Items:**
1. Copy `index.php` from root (it's included in the update)
2. Update any bookmarks/links to use `/Webinar_PIDE/index.php` instead of `/Webinar_PIDE/public/index.php`
3. Keep old `public/index.php` for backwards compatibility (optional)

### Step 5: Update Navigation & Links (5 mins)

Key URL changes:
```
Old: http://localhost/Webinar_PIDE/public/index.php
New: http://localhost/Webinar_PIDE/index.php

Old: http://localhost/Webinar_PIDE/public/yearly_dashboard.php
New: http://localhost/Webinar_PIDE/public/yearly_dashboard.php  (unchanged)

New: http://localhost/Webinar_PIDE/admin/categories.php  (NEW)
New: http://localhost/Webinar_PIDE/admin/pending_webinars.php  (NEW)
New: http://localhost/Webinar_PIDE/public/webinars_list.php  (NEW)
```

### Step 6: Deploy New/Updated Files (5 mins)

Upload these new files:
```
admin/categories.php          (NEW)
admin/pending_webinars.php    (NEW)
public/webinars_list.php      (NEW)
tests/unit_tests.php          (NEW)
index.php                     (NEW, at root)
webinar_app_updated.sql       (NEW, reference)
QUICKSTART.md                 (NEW)
README.md                     (UPDATED)
```

Update these files:
```
includes/config.php                 (Add BASE_URL)
public/navbar.php                   (New menu items)
public/save_event.php               (Approval logic)
public/load_events.php              (Show approved only)
public/update_event.php             (Admin-only edits)
public/delete_event.php             (Restrict deletion)
public/get_event.php                (Include category)
public/fetch_year_events.php        (Approved only)
admin/reports.php                   (Approved only)
```

### Step 7: Test Migration (5 mins)

```bash
# Run unit tests
php tests/unit_tests.php

# Expected output:
# ✓ PASS: Categories table structure
# ✓ PASS: Webinars table approval columns
# ✓ PASS: Config BASE_URL
# ... (10 tests total)
# Summary: 10 passed, 0 failed
```

### Step 8: Verify Application (10 mins)

1. **Login Test**
   - Visit: http://localhost/Webinar_PIDE/index.php
   - Login as admin@example.com
   - Should see calendar

2. **Navigation Test**
   - Check "Categories" link appears
   - Check "Pending Approval" link appears
   - Check "My Webinars" link appears

3. **Calendar Test**
   - Click a time slot
   - Should see new "Category" dropdown
   - Submit a webinar
   - Should go to pending (not visible on calendar)

4. **Admin Test**
   - As admin, visit "Pending Approval"
   - Should see new webinar
   - Click "Approve"
   - Webinar should appear on calendar

## Data Migration Details

### What Happens to Old Webinars

By default, all existing webinars are marked as approved:
```sql
UPDATE webinars SET is_approved=1, approved_by=1, approved_on=NOW();
```

This ensures your calendar stays populated after upgrade.

### What Happens to Categories

If you had categories in v1.0, they may need to be re-created in the new Categories table.

### User Accounts

All user accounts remain unchanged. No re-login required.

## Rollback Plan

If something goes wrong:

### Quick Rollback (Database)
```bash
# Restore database from backup
mysql -u root webinar_app < backup_v1_YYYYMMDD.sql
```

### Full Rollback (Files + Database)
```bash
# Restore entire directory
rm -rf /var/www/Webinar_PIDE
cp -r /var/www/Webinar_PIDE.backup_v1 /var/www/Webinar_PIDE

# Restore database
mysql -u root webinar_app < backup_v1_YYYYMMDD.sql
```

## Feature Enablement

All new features are automatically available after migration.

### For Users
- New "My Webinars" page (automatically shows)
- New category dropdown when submitting
- Approval workflow (automatic)

### For Admins
- New "Categories" menu item
- New "Pending Approval" menu item
- Advanced filters on webinars lists

## Configuration for New Features

### Enable/Disable Approval (Advanced)

The approval workflow is built-in. To mark new webinars as auto-approved:
```php
// In public/save_event.php, change:
// FROM: $ins->execute([$title, $category_id, $start, $end, $_SESSION['user']['id'], 0]);
// TO:   $ins->execute([$title, $category_id, $start, $end, $_SESSION['user']['id'], 1]);
```

### Category Count

Default categories are: Technical, Research, Training, Awareness

Add more via the Categories admin page.

## Performance Considerations

### Database Changes
- New indexes added for approval queries
- Foreign key constraints enforced
- No significant performance impact expected

### Caching
- Category lists are fetched fresh each time
- Consider caching categories for high-traffic sites:
```php
// Option: Add this to includes/config.php
'cache' => [
  'categories' => 300  // 5 minutes
]
```

## Post-Migration Tasks

1. **Update Documentation**
   - Share README.md with users
   - Update internal docs/wiki
   - Create training materials

2. **Notify Users**
   - Email users about new features
   - Provide links to new pages
   - Update bookmarks

3. **Monitor**
   - Check error logs for 24 hours
   - Verify all features work as expected
   - Gather user feedback

4. **Optimize**
   - Consider adding categories to existing webinars
   - Review report statistics
   - Adjust workflows if needed

## Troubleshooting Migration Issues

### Issue: "Missing column" error

**Cause**: Database migration incomplete

**Solution**:
```bash
# Re-run migration with full schema
mysql -u root webinar_app < webinar_app_updated.sql
```

### Issue: "No categories found" dropdown empty

**Cause**: Categories table not populated

**Solution**:
```sql
INSERT INTO categories (title, description, is_active, created_by) VALUES
('Technical', 'Technical webinars', 1, 1),
('Research', 'Research webinars', 1, 1),
('Training', 'Training sessions', 1, 1),
('Awareness', 'Awareness sessions', 1, 1);
```

### Issue: Old webinars don't appear on calendar

**Cause**: Not marked as approved

**Solution**:
```sql
UPDATE webinars SET is_approved=1, approved_by=1, approved_on=NOW() WHERE is_approved=0;
```

### Issue: Permission denied editing files

**Cause**: File permissions not set correctly

**Solution**:
```bash
chmod -R 755 /var/www/Webinar_PIDE
chmod -R 775 /var/www/Webinar_PIDE/uploads  # If upload dir exists
```

### Issue: White page or 500 errors

**Cause**: Configuration error or missing files

**Solution**:
1. Check PHP error logs
2. Verify all new files are uploaded
3. Verify includes/config.php is readable
4. Run: `php tests/unit_tests.php`

## Database Backup Schedule

Recommend setting up automated backups:

```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root webinar_app > /backups/webinar_$DATE.sql
gzip /backups/webinar_$DATE.sql
```

Add to crontab:
```
0 2 * * * /path/to/backup_script.sh
```

## Version Compatibility

| Version | PHP | MySQL | Support |
|---------|-----|-------|---------|
| v1.0 | 7.4+ | 10.3+ | Legacy |
| v2.0 | 8.0+ | 10.4+ | Current |

## Support During Migration

If you encounter issues:

1. Check this guide's Troubleshooting section
2. Run `php tests/unit_tests.php`
3. Review error logs
4. Check README.md for full documentation

## Estimated Migration Time

- **Small Setup** (< 100 webinars): 15 minutes
- **Medium Setup** (100-1000 webinars): 30 minutes
- **Large Setup** (> 1000 webinars): 1 hour
- Plus: Testing and verification

## Sign-Off

After successful migration:

- [ ] Database migrated successfully
- [ ] All new files uploaded
- [ ] Configuration updated
- [ ] Unit tests pass
- [ ] Users can login
- [ ] Calendar displays correctly
- [ ] New features work
- [ ] Old data preserved
- [ ] Backups taken
- [ ] Documentation updated

---

**Migration Guide Version**: 2.0  
**Last Updated**: November 25, 2025  
**Estimated Effort**: 1-2 hours

# Quick Start Guide - PIDE Webinar Portal

Get up and running in 5 minutes!

## Prerequisites
- PHP 8.0+
- MySQL 10.4+
- Web server (Apache/Nginx)

## 5-Minute Setup

### Step 1: Import Database (1 min)
```bash
mysql -u root webinar_app < webinar_app_updated.sql
```

### Step 2: Configure (1 min)
Edit `includes/config.php`:
```php
'base_url' => '/Webinar_PIDE/',
'db' => ['user' => 'root', 'pass' => 'YOUR_PASSWORD']
```

### Step 3: Access (1 min)
```
http://localhost/Webinar_PIDE/index.php
```

### Step 4: Login (1 min)
- **Admin**: admin@example.com / Admin@123
- **User**: user@example.com / User@123

### Step 5: Test (1 min)
```bash
php tests/unit_tests.php
```

## Key Features at a Glance

| Feature | User | Admin |
|---------|------|-------|
| View Calendar | ✓ | ✓ |
| Submit Webinar | ✓ | ✓ |
| Approve Webinars | ✗ | ✓ |
| Manage Categories | ✗ | ✓ |
| View Reports | ✗ | ✓ |
| Edit Approved Webinars | ✗ | ✓ |

## First-Time Actions

### As Admin
1. Go to **Categories** → Add categories
2. Go to **Pending Approval** → Approve/Reject webinars
3. Go to **Reports** → View analytics

### As User
1. Click calendar → Submit webinar
2. Go to **My Webinars** → Track submissions
3. Wait for admin approval

## Navigation Menu

```
Home → [MY WEBINARS] (all users)
     → [CATEGORIES] (admin only)
     → [PENDING APPROVAL] (admin only)
     → [YEARLY DASHBOARD] (admin only)
     → [REPORTS] (admin only)
     → [LOGOUT]
```

## Common Tasks

### Submit Webinar (User)
1. Click calendar time slot
2. Enter Title & select Category
3. Set Start/End times
4. Click "Submit"
5. Wait for approval

### Approve Webinar (Admin)
1. Click "Pending Approval"
2. Review submission
3. Click "Approve"
4. Webinar now visible on calendar

### Reject Webinar (Admin)
1. Click "Pending Approval"
2. Click "Reject"
3. Enter reason (shown to user)
4. Submit

### Filter Webinars (User/Admin)
1. Click "My Webinars"
2. Use filters: Date, Category, Search
3. For Admin: Also filter by User
4. Click "Search"

### View Reports (Admin)
1. Click "Reports"
2. Select Year
3. View charts and statistics
4. Click "Export PDF"

## Default Test Accounts

| Email | Password | Role |
|-------|----------|------|
| admin@example.com | Admin@123 | Admin |
| user@example.com | User@123 | User |

*Change these in production!*

## Database Tables

### Users
- **id**, name, email, password_hash, role, created_at

### Categories (NEW)
- **id**, title, description, is_active, created_by, created_on, modified_by, modified_on

### Webinars (UPDATED)
- **id**, title, start_at, end_at, initiated_by
- **NEW**: category_id, is_approved, approved_by, approved_on, rejection_reason

## File Locations

| Feature | File |
|---------|------|
| Calendar | /index.php |
| Categories | /admin/categories.php |
| Approvals | /admin/pending_webinars.php |
| My Webinars | /public/webinars_list.php |
| Reports | /admin/reports.php |
| Dashboard | /public/yearly_dashboard.php |

## Troubleshooting

### "No categories found"
→ Login as admin → Categories → Add categories

### "Can't edit webinar"
→ Only admins can edit approved webinars

### "Webinar not showing on calendar"
→ It's not approved yet → Check Pending Approval

### "Database connection error"
→ Check credentials in includes/config.php

## Testing

```bash
# Run all unit tests
php tests/unit_tests.php

# Output shows:
✓ Database schema verified
✓ Configuration valid
✓ All required files present
✓ Permissions correct
```

## API Endpoints (For Developers)

```
GET   /public/load_events.php              - List approved webinars
POST  /public/save_event.php               - Submit new webinar
POST  /public/update_event.php             - Update webinar (admin)
POST  /public/delete_event.php             - Delete webinar
GET   /public/get_event.php?id=<id>        - Get event details
GET   /public/fetch_year_events.php?year=Y - Year statistics
```

## Configuration Reference

File: `includes/config.php`

```php
return [
  // Website base URL (if not at domain root)
  'base_url' => '/Webinar_PIDE/',
  
  // Database connection
  'db' => [
    'host' => '127.0.0.1',    // Change if remote
    'port' => 3306,           // MySQL port
    'name' => 'webinar_app',  // Database name
    'user' => 'root',         // Database user
    'pass' => ''              // Database password
  ],
  
  // Google OAuth (optional)
  'google' => [
    'client_id' => '...',
    'client_secret' => '...',
    'redirect_uri' => '...'
  ]
];
```

## Performance Tips

1. **Enable caching**: Cache category lists
2. **Limit query results**: Pagination for large datasets
3. **Use indexes**: DB already optimized
4. **Browser cache**: Static assets cached well
5. **Disable debug**: Set error reporting to log only

## Security Checklist

- [ ] Change admin password
- [ ] Configure firewall
- [ ] Enable HTTPS
- [ ] Set strong DB password
- [ ] Regular backups
- [ ] Monitor error logs
- [ ] Keep PHP updated

## Next Steps

1. ✓ Read this guide
2. ✓ Run quick setup
3. → Explore features in UI
4. → Create test webinars
5. → Check Reports & Dashboard
6. → Read full README.md for advanced topics

## Support Resources

- **Full Documentation**: README.md
- **Database Schema**: webinar_app_updated.sql
- **Tests**: php tests/unit_tests.php
- **Error Logs**: Check server/PHP logs

---

**Version**: 2.0  
**Last Updated**: November 25, 2025  
**Status**: Ready to Use ✓

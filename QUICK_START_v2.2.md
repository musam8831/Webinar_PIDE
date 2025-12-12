# PIDE Webinar Application - Quick Start Guide v2.2

**Version**: 2.2  
**Last Updated**: December 12, 2025  
**Status**: ✓ Production Ready

---

## Quick Navigation

- [Installation](#installation)
- [First Time Setup](#first-time-setup)
- [Running Tests](#running-tests)
- [Creating a Webinar](#creating-a-webinar)
- [Admin Workflow](#admin-workflow)
- [Troubleshooting](#troubleshooting)
- [What's New in v2.2](#whats-new-in-v22)

---

## Installation

### System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or MariaDB 10.3+
- Web server (Apache, Nginx)
- Web browser (Chrome, Firefox, Safari, Edge)

### Step 1: Database Setup

```bash
# Navigate to project directory
cd c:\xampp-updated\htdocs\Webinar_PIDE

# Import the schema
mysql -u root -p < webinar_app.sql
```

### Step 2: Configuration

Edit `includes/config.php`:

```php
define('BASE_URL', 'http://localhost/Webinar_PIDE/');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'webinar_app');
```

### Step 3: Verify Installation

Visit: `http://localhost/Webinar_PIDE/`

---

## First Time Setup

### 1. Create Admin User

```bash
php setup.php
```

Follow the prompts to create an admin account.

### 2. Run Tests

```bash
php tests/unit_tests.php
```

Expected output:
```
Summary: 22 passed, 0 failed
Success Rate: 100%
```

### 3. Create Categories

Login as admin → Navigate to Settings → Add categories:
- Technology
- Business
- Education
- Other

### 4. Test the System

Create a webinar and verify:
- [ ] Notification appears
- [ ] Webinar shows in your calendar
- [ ] Admin can see it in pending list
- [ ] Colors are correct

---

## Running Tests

### Full Test Suite

```bash
cd c:\xampp-updated\htdocs\Webinar_PIDE
php tests/unit_tests.php
```

### Expected Results

```
========================================
PIDE Webinar Application - Unit Tests
========================================

Test 1: Categories table structure [✓ PASS]
Test 2: Webinars table approval columns [✓ PASS]
...
Test 22: Pending webinars excludes rejected [✓ PASS]

Summary: 22 passed, 0 failed
Total: 22 tests
Success Rate: 100%
```

---

## Creating a Webinar

### For Regular Users

1. **Login** to the application
2. **Click** "Create Webinar" button
3. **Fill in**:
   - Title
   - Description
   - Category
   - Start Date & Time
   - End Date & Time
4. **Click** "Save Webinar"
5. **See** green notification: "Webinar submitted for approval"
6. **View** webinar in orange (pending) on calendar

### Key Points

- ✓ Only you can see your pending webinars
- ✓ Admins can also see all pending webinars
- ✓ You can edit pending webinars
- ✓ You **cannot** edit approved webinars
- ✓ You **cannot** delete approved webinars

---

## Admin Workflow

### Approving Webinars

1. **Login** as admin
2. **Click** "Pending Webinars" in admin menu
3. **Review** the webinar details
4. **Click** "Approve" button
5. **See** webinar turns green on calendar

### Rejecting Webinars

1. **In Pending Webinars page**
2. **Click** "Reject" button
3. **Enter** rejection reason (required)
   - Example: "Please add more details to the description"
4. **Click** "Reject" button
5. **Webinar status changes to red** (rejected)
6. **Creator can see** the rejection reason in webinars list

### What Changed in v2.2

**Before v2.2**:
- Reject button would delete the webinar
- Creator couldn't see why it was rejected
- No record kept in database

**After v2.2**:
- Reject button **only updates** the rejection reason
- Webinar **stays in database**
- Creator can see rejection reason
- Full audit trail maintained

---

## Permission System

### For Regular Users

| Action | Own Pending | Own Approved | Other's | Status |
|--------|------------|-------------|---------|--------|
| View | ✓ Yes | ✓ Yes | ❌ No (pending) | v2.2 |
| Edit | ✓ Yes | ❌ No | ❌ No | v2.2 |
| Delete | ✓ Yes | ❌ No | ❌ No | v2.2 |

### For Admins

| Action | Own | Others' | Approved | Status |
|--------|-----|---------|----------|--------|
| View | ✓ Yes | ✓ Yes | ✓ Yes | v2.2 |
| Edit | ✓ Yes | ✓ Yes | ✓ Yes | v2.2 |
| Delete | ✓ Yes | ✓ Yes | ✓ Yes | v2.2 |

---

## Webinar Status Explanation

### Three Status System (v2.2)

**Status 1: PENDING** (Orange)
- Webinar not yet approved
- No rejection reason set
- Creator can edit/delete
- Visible only to creator and admin

**Status 2: APPROVED** (Green)
- Webinar approved by admin
- Visible to everyone (public)
- Creator **cannot** edit/delete
- Only admin can edit/delete

**Status 3: REJECTED** (Red)
- Webinar rejected with reason
- Shows rejection reason to creator
- Visible only to creator and admin
- Creator can edit and resubmit

### Status Color Legend

| Color | Status | Meaning |
|-------|--------|---------|
| 🟢 Green | Approved | Ready and public |
| 🟠 Orange | Pending | Waiting for approval |
| 🔴 Red | Rejected | Needs revision |

---

## Common Tasks

### Task: Create and Submit a Webinar

```
1. Login → Home
2. Click "Create Webinar"
3. Fill in all fields
4. Click "Save Webinar"
5. See green notification ✓
6. Webinar appears in orange on calendar ✓
```

**Result**: Notification shows "Webinar submitted for approval"

### Task: Admin Approves a Webinar

```
1. Login as Admin → Admin Dashboard
2. Click "Pending Webinars"
3. Review webinar
4. Click "Approve"
5. See success message ✓
```

**Result**: 
- Webinar turns green ✓
- Appears on public calendar ✓
- Creator notified ✓

### Task: Admin Rejects a Webinar

```
1. In Pending Webinars
2. Click "Reject"
3. Enter reason: "Add speaker information"
4. Click "Reject" button
5. See success message ✓
```

**Result**:
- Webinar turns red ✓
- Rejection reason saved ✓
- Creator sees reason in list ✓
- Can edit and resubmit ✓

### Task: User Sees Rejection Reason

```
1. Login as creator
2. Go to "My Webinars"
3. Click on red (rejected) webinar
4. See rejection reason ✓
```

**Result**: Tooltip shows "Rejection Reason: Add speaker information"

### Task: Creator Edits Rejected Webinar

```
1. Find rejected (red) webinar
2. Click "Edit" button ✓
3. Modify content
4. Click "Save Webinar"
5. See green notification ✓
```

**Result**: Webinar back to orange (pending) status ✓

---

## Notification System (v2.2)

### Types of Notifications

**Success** (Green)
- "Webinar submitted for approval"
- "Webinar updated successfully"
- "Webinar deleted successfully"

**Error** (Red)
- "Failed to save webinar"
- "Unauthorized action"

**Info** (Blue)
- "Loading events..."
- "Calendar updated"

### Notification Features

- ✓ Appears in top-right corner
- ✓ Auto-dismisses after 5 seconds
- ✓ Smooth animations
- ✓ Clear message text
- ✓ Can click to dismiss early

---

## File Structure

```
Webinar_PIDE/
├── index.php                 # Main calendar interface
├── setup.php                 # Initial setup
├── schema.sql                # Database schema
│
├── includes/
│   ├── config.php           # Configuration settings
│   ├── auth.php             # Authentication logic
│   └── db.php               # Database connection
│
├── public/
│   ├── login.php            # Login page
│   ├── load_events.php      # Calendar events (JSON)
│   ├── webinars_list.php    # Webinars list view
│   ├── save_event.php       # Create webinar
│   ├── update_event.php     # Update webinar
│   └── delete_event.php     # Delete webinar
│
├── admin/
│   ├── pending_webinars.php # Pending approval list
│   ├── users.php            # User management
│   └── reports.php          # Reports view
│
├── assets/
│   └── css/
│       ├── styles.css       # Main styles
│       └── PIDETheme.css    # Theme colors
│
└── tests/
    └── unit_tests.php       # Unit tests
```

---

## What's New in v2.2

### New Features

1. **Permission Restrictions** ✓
   - Users can't edit approved webinars
   - Only admins can modify approved webinars
   - Better control and security

2. **Fixed Reject Button** ✓
   - Now updates instead of deletes
   - Saves rejection reason
   - Preserves data for audit trail

3. **Three-Status System** ✓
   - Clear: Approved (green), Pending (orange), Rejected (red)
   - Better user understanding
   - Improved navigation

4. **Rejection Reason Display** ✓
   - Creators can see why webinar was rejected
   - Helps improve submissions
   - Complete transparency

### Bug Fixes

1. **Permission Lock** ✓ FIXED
   - Users can no longer edit approved webinars
   - Clear indication when permissions denied

2. **Data Preservation** ✓ FIXED
   - Rejected webinars no longer deleted
   - Keeps audit trail intact
   - Records preserved for admin review

3. **Pending List Accuracy** ✓ FIXED
   - Only truly pending webinars shown
   - Rejected webinars filtered out
   - Cleaner workflow

### Test Coverage

**Added 5 new tests**:
- Test 18: Edit/Delete permissions
- Test 19: Reject button behavior
- Test 20: Three-status system
- Test 21: Rejection reason display
- Test 22: Pending list filtering

**Total**: 22 tests, all passing ✓

---

## Troubleshooting

### Problem: Notification not showing

**Solution**:
1. Check browser console for errors
2. Verify CSS file is loaded
3. Check if JavaScript is enabled
4. Clear browser cache

**Code Location**: Check `index.php` lines 17-45

---

### Problem: Can't edit my webinar

**Check**: Is it approved?
- If **green** (approved): Only admins can edit
- If **orange** (pending): You can edit ✓
- If **red** (rejected): You can edit ✓

**How to fix**: Contact admin to reject it first

---

### Problem: Tests are failing

**Steps**:
1. Verify database is running
2. Check database credentials in `config.php`
3. Verify `webinar_app` database exists
4. Run: `php setup.php`
5. Try tests again

```bash
php tests/unit_tests.php
```

---

### Problem: Permission denied for action

**Check**:
1. Are you logged in?
2. Are you the creator?
3. Is webinar approved?

**Rules**:
- Non-admin: Only edit own unapproved webinars
- Admin: Can edit any webinar

---

## Dashboard Overview

### For Regular Users

**Home Page Features**:
- Calendar view of all approved webinars
- Color-coded by status
- "Create Webinar" button
- "My Webinars" link to view your submissions

**My Webinars Page**:
- All your webinars (pending, approved, rejected)
- Status clearly shown (color + badge)
- Edit button (if allowed)
- Delete button (if allowed)
- View rejection reason (if rejected)

### For Admins

**Admin Dashboard Features**:
- "Pending Webinars" - Review unapproved submissions
- "Users" - Manage user accounts
- "Reports" - Analytics and statistics
- "Categories" - Manage webinar categories

**Pending Webinars Page**:
- List of all unapproved webinars
- Approve button for each
- Reject button with reason field
- Shows webinar details
- Creator information

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + S` | Save webinar (in form) |
| `Esc` | Close dialog/modal |
| `Enter` | Submit form |
| `Tab` | Navigate form fields |

---

## Best Practices

### For Users

1. **Provide complete information**
   - Clear title
   - Detailed description
   - Correct start/end times

2. **Use correct category**
   - Helps with discoverability
   - Improves organization

3. **Respond to rejections**
   - Read rejection reason carefully
   - Make requested changes
   - Resubmit promptly

### For Admins

1. **Review webinars thoroughly**
   - Check content quality
   - Verify speaker information
   - Ensure proper categorization

2. **Provide clear rejection reasons**
   - Specific feedback
   - Actionable improvements
   - Constructive tone

3. **Approve promptly**
   - Keep pending list current
   - Encourage submissions
   - Quick turnaround

---

## Advanced Features

### Search (in Webinars List)

- Search by title
- Search by description
- Search by category
- Filters by status (Approved/Pending/Rejected/All)

### Calendar Views

- Month view (default)
- Week view (click week)
- Day view (click date)
- Event tooltips with details

### Export Features

- (Coming in v2.3)
- Export calendar as ICS
- Export webinars as CSV
- Generate attendance reports

---

## Support & Documentation

| Document | Purpose |
|----------|---------|
| UPDATE_SUMMARY_v2.2.md | What's new in v2.2 |
| TESTING_DOCUMENTATION_v2.2.md | Detailed test information |
| IMPLEMENTATION_CHECKLIST_v2.2.md | Complete requirement checklist |
| API_DOCUMENTATION.md | API endpoints and usage |

---

## Version Timeline

**v2.0** - Initial Release
- Basic webinar management
- Admin approval system

**v2.1** - Enhanced Features (Dec 11)
- Unapproved visibility ✓
- Color scheme ✓
- Notifications ✓
- Rejection reason ✓
- Three-status system ✓

**v2.2** - Bug Fixes & Improvements (Dec 12)
- Permission restrictions ✓
- Reject button fix ✓
- Pending list accuracy ✓
- 5 new unit tests ✓

---

## Next Steps After Setup

1. ✓ Run `php tests/unit_tests.php` - Verify all tests pass
2. ✓ Create test categories
3. ✓ Create test webinar (as regular user)
4. ✓ Approve it (as admin)
5. ✓ Verify color scheme works
6. ✓ Verify permissions work
7. ✓ Test rejection workflow

---

## Frequently Asked Questions

**Q: Can I edit my webinar after it's approved?**  
A: No. Once approved, only admins can edit. This is by design for data integrity.

**Q: What happens to my webinar if it's rejected?**  
A: It stays in your list with a red indicator. You can see the rejection reason and edit it to resubmit.

**Q: Who can see my pending webinar?**  
A: Only you (the creator) and admins. Regular users can't see it.

**Q: Can I delete an approved webinar?**  
A: No. You must ask an admin to delete it.

**Q: What if I disagree with a rejection?**  
A: Contact your admin to discuss. They can approve it or provide more feedback.

---

## Contact & Support

For technical issues or questions:

1. Check this Quick Start guide
2. Review TESTING_DOCUMENTATION_v2.2.md
3. Check error logs in browser console
4. Contact system administrator

---

## Next Release Plans (v2.3)

Planned features for next version:
- Rejection history/versioning
- Automatic notifications for creators
- Advanced analytics dashboard
- Bulk operations for admins
- Email notifications
- Calendar sharing
- Webinar templates

---

**Quick Start Version**: 2.2  
**Last Updated**: December 12, 2025  
**Status**: ✓ PRODUCTION READY

Ready to get started? Begin with [Installation](#installation) above!

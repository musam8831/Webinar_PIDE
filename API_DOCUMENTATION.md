# API Documentation - PIDE Webinar Portal

Complete API reference for developers integrating with or extending the PIDE Webinar Portal.

## Base URL

```
http://localhost/Webinar_PIDE/
```

All API endpoints are relative to this URL. Replace `/Webinar_PIDE/` with your `BASE_URL` configuration.

## Authentication

All endpoints require active PHP session (user must be logged in).

### Session Authentication
- Login via: `/public/login.php` or `/public/google_login.php`
- Session stored in: `$_SESSION['user']`
- User object contains: `id`, `name`, `email`, `role`

## API Endpoints

### 1. Load Approved Events (Calendar)

**Endpoint**: `public/load_events.php`
**Method**: GET
**Auth**: Required
**Cache**: Can be cached 5-15 minutes

**Response** (JSON Array):
```json
[
  {
    "id": 1,
    "title": "Team Meeting",
    "start": "2025-01-06 09:00:00",
    "end": "2025-01-06 10:00:00",
    "extendedProps": {
      "initiator_name": "Administrator",
      "initiator_email": "admin@example.com",
      "category_title": "Technical",
      "can_edit": true,
      "can_delete": false
    }
  }
]
```

**Notes**:
- Shows only `is_approved=1` webinars
- Only admins can edit (`can_edit=true`)
- Returns UTC time in database format
- FullCalendar converts to local timezone

---

### 2. Create Webinar (Submit for Approval)

**Endpoint**: `public/save_event.php`
**Method**: POST
**Auth**: Required
**Role**: User or Admin

**Request Body** (JSON):
```json
{
  "title": "New Workshop",
  "category_id": 1,
  "start": "2025-01-15T10:00",
  "end": "2025-01-15T12:00"
}
```

**Response Success** (201-ish):
```json
{
  "ok": true,
  "id": 26
}
```

**Response Error** (422/409):
```json
{
  "error": "Selected slot overlaps an existing approved webinar"
}
```

**Validation**:
- `title`: Required, non-empty string
- `category_id`: Required, valid active category
- `start` & `end`: Required, datetime-local format (browser local time)
- No overlaps with approved webinars allowed

**New Webinar State**:
- `is_approved`: 0 (unapproved)
- Visible to: Admin only (in pending list)
- Not shown on calendar

**Status Codes**:
- `200`: Success
- `422`: Validation error (missing/invalid fields)
- `409`: Conflict (overlap detected)

---

### 3. Get Event Details

**Endpoint**: `public/get_event.php?id={id}`
**Method**: GET
**Auth**: Required
**Role**: Any

**Query Parameters**:
- `id` (required): Event ID

**Response**:
```json
{
  "id": 1,
  "title": "Team Meeting",
  "category_id": 1,
  "start": "2025-01-06 09:00",
  "end": "2025-01-06 10:00",
  "start_local": "2025-01-06T09:00",
  "end_local": "2025-01-06T10:00",
  "initiator_name": "Administrator",
  "initiator_email": "admin@example.com",
  "can_edit": true,
  "can_delete": false
}
```

**Notes**:
- `start/end`: UTC format
- `start_local/end_local`: Browser timezone (for form pre-fill)
- `can_edit`: Only true for admins on approved webinars
- `can_delete`: Depends on ownership and approval status

---

### 4. Update Event (Admin Only)

**Endpoint**: `public/update_event.php`
**Method**: POST
**Auth**: Required
**Role**: Admin only

**Request Body** (JSON):
```json
{
  "id": 1,
  "title": "Updated Workshop",
  "category_id": 2,
  "start": "2025-01-15T10:00",
  "end": "2025-01-15T12:00"
}
```

**Response Success**:
```json
{
  "ok": true
}
```

**Response Error**:
```json
{
  "error": "Only admin can edit webinars"
}
```

**Permissions**:
- Only admin users can update
- Can update title, category, start/end times
- Overlap checking excludes current event
- Updating only approved webinars recommended

**Validation Rules**:
- Title: Non-empty string
- Category: Active category ID
- End time must be after start time
- No overlaps with other approved webinars

---

### 5. Delete Event

**Endpoint**: `public/delete_event.php`
**Method**: POST
**Auth**: Required

**Request Body** (JSON):
```json
{
  "id": 1
}
```

**Response Success**:
```json
{
  "ok": true
}
```

**Response Error** (403):
```json
{
  "error": "You can only delete your own unapproved webinars"
}
```

**Delete Rules**:
- **Users**: Can delete only their own unapproved webinars
- **Admin**: Can delete any webinar
- **Approved webinars**: Cannot be deleted by non-admins

---

### 6. Fetch Year Events (Reports/Dashboard)

**Endpoint**: `public/fetch_year_events.php?year={year}`
**Method**: GET
**Auth**: Required (admin)
**Role**: Admin only

**Query Parameters**:
- `year` (optional): 4-digit year (default: current year)

**Response** (JSON Array):
```json
[
  {
    "id": 1,
    "title": "Team Meeting",
    "date": "2025-01-06",
    "start": "09:00",
    "end": "10:00",
    "initiator": "Administrator"
  }
]
```

**Notes**:
- Returns only approved webinars for specified year
- Date format: YYYY-MM-DD
- Time format: HH:MM (24-hour, UTC)
- Used by dashboard and reports
- Can be cached aggressively

---

## Category Management APIs

### 7. List Categories

**Endpoint**: `admin/categories.php` (GET with no action)
**Method**: GET/POST (GET for list view)
**Auth**: Required (admin)
**Page-based**: Not an API endpoint, but returns HTML with data

**Alternative** (Query directly):
```sql
SELECT id, title, description, is_active, created_by, created_on 
FROM categories 
WHERE is_active=1 
ORDER BY title
```

### 8. Add Category

**Endpoint**: `admin/categories.php` (POST)
**Method**: POST
**Auth**: Required (admin)

**Form Data**:
```
POST /admin/categories.php
Content-Type: application/x-www-form-urlencoded

action_type=add&title=New+Category&description=...&is_active=1
```

**Response**: Redirect with success/error message

---

## Webinars List with Filters

### 9. List Webinars (with Filters)

**Endpoint**: `public/webinars_list.php`
**Method**: GET
**Auth**: Required

**Query Parameters**:
```
GET /public/webinars_list.php?user=1&date=today&category=1&search=workshop&from=2025-01-01&to=2025-01-31
```

Supported parameters:
- `user`: User ID (admin only) - Default: current user
- `date`: 'all', 'today', 'week', 'month', 'year', 'custom'
- `category`: Category ID
- `search`: Text search (title or user name)
- `from`: Start date (YYYY-MM-DD) when date=custom
- `to`: End date (YYYY-MM-DD) when date=custom

**Example Queries**:

Today's webinars:
```
/public/webinars_list.php?date=today
```

Custom date range:
```
/public/webinars_list.php?date=custom&from=2025-01-01&to=2025-12-31
```

By category and search:
```
/public/webinars_list.php?category=1&search=technical
```

---

## Approval Management APIs

### 10. Approve Webinar

**Endpoint**: `admin/pending_webinars.php` (POST)
**Method**: POST
**Auth**: Required (admin)

**Form Data**:
```
action=approve&webinar_id=1
```

**Response**: Redirect to same page with success message

---

### 11. Reject Webinar

**Endpoint**: `admin/pending_webinars.php` (POST)
**Method**: POST
**Auth**: Required (admin)

**Form Data**:
```
action=reject&webinar_id=1&reason=Does+not+match+category+guidelines
```

**Response**: Redirect to same page with success message

**Notes**:
- Webinar is deleted after rejection
- Reason is just for admin reference (not stored permanently)
- User doesn't receive rejection notification (future enhancement)

---

## Response Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | Event created successfully |
| 201 | Created | New resource created |
| 400 | Bad Request | Malformed JSON |
| 403 | Forbidden | Admin-only endpoint, user tried |
| 404 | Not Found | Event ID doesn't exist |
| 409 | Conflict | Overlap detected |
| 422 | Unprocessable | Validation failed |
| 500 | Server Error | Database error |

## Content Type

All API endpoints:
- **Accept**: `application/json`
- **Return**: `application/json`

Headers:
```
Content-Type: application/json
```

## Error Responses

Standard error format:
```json
{
  "error": "Human-readable error message"
}
```

Examples:
```json
{
  "error": "All fields are required"
}
```

```json
{
  "error": "Selected slot overlaps an existing approved webinar"
}
```

---

## Data Types & Formats

### Date/Time Formats

| Format | Example | Usage |
|--------|---------|-------|
| UTC DateTime | 2025-01-06 09:00:00 | Database storage |
| Datetime-local | 2025-01-06T09:00 | HTML form input |
| ISO 8601 | 2025-01-06T09:00:00Z | JSON API |
| Date only | 2025-01-06 | Reports |
| Time only | 09:00 | Dashboard |

### User Roles

| Role | Permissions |
|------|-------------|
| admin | Create, read, update, delete any webinar; approve submissions; manage categories |
| user | Create (submit) webinars; edit own unapproved; delete own unapproved; view only approved |

### Webinar Approval States

| State | is_approved | Visibility |
|-------|-------------|------------|
| Pending | 0 | Pending list only |
| Approved | 1 | Calendar, reports, lists |
| Rejected | -1 (deleted) | Not visible |

---

## Rate Limiting

Currently: None implemented

Recommended for production:
- 100 requests per minute per user
- 1000 requests per minute per IP

---

## Caching

Recommended caching strategies:

| Endpoint | Duration | Invalidate On |
|----------|----------|---------------|
| load_events.php | 5-15 min | New approval, create, update, delete |
| fetch_year_events.php | 1 hour | Approval changes |
| categories list | 1 hour | Category CRUD |

---

## Integration Examples

### JavaScript/AJAX

Fetch approved events:
```javascript
fetch('public/load_events.php')
  .then(r => r.json())
  .then(events => console.log(events));
```

Submit webinar:
```javascript
fetch('public/save_event.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Workshop',
    category_id: 1,
    start: '2025-01-15T10:00',
    end: '2025-01-15T12:00'
  })
})
.then(r => r.json())
.then(data => console.log(data.id));
```

Update event:
```javascript
fetch('public/update_event.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id: 1,
    title: 'Updated Title',
    category_id: 2,
    start: '2025-01-15T11:00',
    end: '2025-01-15T13:00'
  })
})
.then(r => r.json())
.then(data => console.log(data.ok));
```

### PHP

```php
$ch = curl_init('http://localhost/Webinar_PIDE/public/load_events.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
$response = curl_exec($ch);
$events = json_decode($response, true);
```

---

## Database Query Examples

Query approved webinars for a user:
```sql
SELECT w.*, c.title AS category_title, u.name
FROM webinars w
LEFT JOIN categories c ON c.id = w.category_id
JOIN users u ON u.id = w.initiated_by
WHERE w.is_approved = 1 AND w.initiated_by = 2
ORDER BY w.start_at DESC;
```

Query pending approvals:
```sql
SELECT w.*, u.name AS initiator_name, c.title AS category_title
FROM webinars w
JOIN users u ON u.id = w.initiated_by
LEFT JOIN categories c ON c.id = w.category_id
WHERE w.is_approved = 0
ORDER BY w.created_at DESC;
```

Query reports data:
```sql
SELECT u.name, COUNT(w.id) AS count
FROM users u
LEFT JOIN webinars w ON w.initiated_by = u.id AND w.is_approved = 1
WHERE YEAR(w.start_at) = 2025
GROUP BY u.id
ORDER BY count DESC;
```

---

## Versioning

Current API Version: **2.0**

Breaking changes from v1.0:
- All webinars now require approval before display
- New `category_id` field required on create
- `load_events.php` returns only approved webinars
- `update_event.php` admin-only (was open to owner)
- New approval-related endpoints

---

## Security

### Input Validation
- All strings trimmed and checked for emptiness
- IDs validated as integers
- DateTime validated as valid format
- SQL queries use prepared statements

### Authorization
- Admin-only endpoints check `$_SESSION['user']['role']==='admin'`
- CRUD permissions verified per request
- Session hijacking mitigated by server-side session

### Data Protection
- Passwords hashed with bcrypt
- No sensitive data in API responses
- HTTPS recommended for production
- CSRF protection recommended

---

## Support & Troubleshooting

### 405 Method Not Allowed
- Endpoint only accepts certain HTTP methods
- Check endpoint documentation for correct method

### 403 Forbidden
- You don't have permission for this action
- Check user role and authorization requirements

### 422 Unprocessable Entity
- Request data doesn't meet validation requirements
- Check error message for specific field issues

### 409 Conflict
- Usually overlap detected
- Verify requested time slot doesn't conflict with approved webinars

---

## Future Enhancements

Planned for future versions:
- GraphQL API
- Webhook support for approvals
- Export to iCal format
- Rate limiting
- API key authentication
- Pagination support
- Bulk operations

---

**API Documentation Version**: 2.0  
**Last Updated**: November 25, 2025  
**Status**: Production Ready

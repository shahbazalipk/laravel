# SSO Implementation Guide

## Overview

This event subdomain application now supports Single Sign-On (SSO) authentication from the main organization portal at `https://app.glimzo.ai`.

## How It Works

1. User clicks "Login as" button in the organization portal
2. Organization portal generates a one-time SSO token (5-minute expiry)
3. User is redirected to: `https://{subdomain}.glimzo.ai/admin/login?sso_token={token}`
4. Event subdomain validates the token with the organization portal API
5. If valid, user is automatically logged in and redirected to the admin dashboard

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Organization Portal URL for SSO authentication
ORG_PORTAL_URL=https://app.glimzo.ai

# Event and Organization IDs (must match the SSO token data)
EVENT_ID=18
ORG_ID=5
```

### Local Development

For local testing, update the `ORG_PORTAL_URL` to point to your local organization portal:

```env
ORG_PORTAL_URL=http://127.0.0.1:8000
```

## Security Features

1. **One-time use tokens**: Each SSO token can only be used once
2. **Short expiry**: Tokens expire after 5 minutes
3. **Event validation**: Users can only access events they have permission for
4. **Organization validation**: Organization ID must match the current event
5. **HTTPS required**: All production communication uses HTTPS

## API Endpoint

The event subdomain calls the following API endpoint to validate SSO tokens:

**Endpoint**: `POST {ORG_PORTAL_URL}/api/validate-sso-token`

**Request**:
```json
{
  "token": "abc123xyz..."
}
```

**Success Response (200)**:
```json
{
  "success": true,
  "user": {
    "user_id": 123,
    "organization_id": 5,
    "event_id": 18,
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

**Error Response (401)**:
```json
{
  "error": "Invalid or expired token"
}
```

## Database Changes

The `admins` table now includes:
- `organization_id` - Links admin to organization
- `event_id` - Links admin to specific event
- `last_login_at` - Tracks last login timestamp

These fields are automatically populated during SSO login.

## User Flow

### SSO Login
1. User arrives at `/admin/login?sso_token=xxx`
2. System validates token with organization portal
3. System checks event and organization permissions
4. Admin user is created/updated in local database
5. Session is created with admin credentials
6. User is redirected to `/admin/dashboard`

### Regular Login
Regular email/password login still works as before for local admin accounts.

## Error Handling

The system handles various error scenarios:

- **Invalid token**: User sees "Invalid or expired login link"
- **Wrong event**: User sees "Access denied: You do not have permission to access this event"
- **Wrong organization**: User sees "Access denied: Organization mismatch"
- **API unavailable**: User sees "Authentication service unavailable" with option to use regular login

All errors are logged for debugging purposes.

## Testing

### Test SSO Login

1. Ensure organization portal is running
2. Create an SSO token in the organization portal
3. Visit: `http://127.0.0.1:8001/admin/login?sso_token={token}`
4. Verify automatic login and redirect to dashboard

### Test Regular Login

Regular login should continue to work:
1. Visit: `http://127.0.0.1:8001/admin/login`
2. Enter email and password
3. Verify login works as before

## Troubleshooting

### Token validation fails
- Check that `ORG_PORTAL_URL` is correctly configured
- Verify network connectivity to organization portal
- Check logs for detailed error messages
- Ensure token hasn't expired (5-minute window)

### User not created
- Verify database connection
- Check that admins table exists and has required columns
- Run migrations: `php artisan migrate`

### Session not persisting
- Check session configuration in `.env`
- Verify session driver is properly configured
- Clear cache: `php artisan cache:clear`

## Files Modified

- `app/Http/Controllers/Admin/AuthController.php` - Added SSO login handling
- `app/Models/Admin.php` - Added organization_id, event_id, last_login_at fields
- `config/app.php` - Added org_portal_url configuration
- `database/migrations/2026_02_17_193041_create_admins_table_if_not_exists.php` - Migration for new fields
- `.env` - Added ORG_PORTAL_URL configuration

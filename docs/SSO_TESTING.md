# SSO Testing Guide

## Quick Test

To test the SSO implementation, you can simulate an SSO login by visiting:

```
http://127.0.0.1:8001/admin/login?sso_token=YOUR_TOKEN_HERE
```

## Expected Behavior

### Successful SSO Login

When a valid SSO token is provided:

1. System validates token with organization portal API
2. Checks that event_id and org_id match
3. Creates or updates admin user in local database
4. Creates session for the user
5. Redirects to `/admin/dashboard` with success message

### Failed SSO Login

When an invalid token is provided:

1. System attempts to validate token
2. Receives error from organization portal
3. Redirects to `/admin/login` with error message
4. User can still use regular login

## Test Scenarios

### Scenario 1: Valid SSO Token

**URL**: `http://127.0.0.1:8001/admin/login?sso_token=VALID_TOKEN`

**Expected Result**:
- User is logged in automatically
- Redirected to admin dashboard
- Success message: "Welcome back, {name}!"

### Scenario 2: Invalid/Expired Token

**URL**: `http://127.0.0.1:8001/admin/login?sso_token=INVALID_TOKEN`

**Expected Result**:
- User stays on login page
- Error message: "Invalid or expired login link. Please try again."
- Regular login form is still available

### Scenario 3: Wrong Event ID

**URL**: `http://127.0.0.1:8001/admin/login?sso_token=TOKEN_FOR_DIFFERENT_EVENT`

**Expected Result**:
- User stays on login page
- Error message: "Access denied: You do not have permission to access this event."

### Scenario 4: Organization Portal Unavailable

**URL**: `http://127.0.0.1:8001/admin/login?sso_token=ANY_TOKEN`
(with organization portal down)

**Expected Result**:
- User stays on login page
- Error message: "Authentication service unavailable. Please try again later or use regular login."

### Scenario 5: Regular Login (No SSO Token)

**URL**: `http://127.0.0.1:8001/admin/login`

**Expected Result**:
- Normal login form is displayed
- User can login with email/password
- SSO functionality doesn't interfere

## Checking Logs

To debug SSO issues, check the Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
- `SSO login successful`
- `SSO validation failed`
- `SSO access denied`
- `SSO authentication error`

## Database Verification

After successful SSO login, verify the admin user was created:

```sql
SELECT * FROM admins WHERE email = 'user@example.com';
```

Check that these fields are populated:
- `organization_id`
- `event_id`
- `last_login_at`

## Session Verification

After successful login, check that session contains:
- `admin_logged_in` = true
- `admin_id` = user's ID
- `admin_email` = user's email
- `admin_name` = user's name
- `sso_login` = true (for SSO logins)

## API Call Verification

You can manually test the API call using curl:

```bash
curl -X POST https://app.glimzo.ai/api/validate-sso-token \
  -H "Content-Type: application/json" \
  -d '{"token":"YOUR_TOKEN_HERE"}'
```

Expected response for valid token:
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

## Common Issues

### Issue: "Call to undefined method"
**Solution**: Run migrations: `php artisan migrate`

### Issue: "Connection timeout"
**Solution**: Check `ORG_PORTAL_URL` in `.env` file

### Issue: "Invalid event"
**Solution**: Verify `EVENT_ID` and `ORG_ID` in `.env` match the token data

### Issue: "Session not persisting"
**Solution**: 
- Check session configuration
- Clear cache: `php artisan cache:clear`
- Restart server

## Production Testing

Before deploying to production:

1. Update `.env` with production values:
   ```env
   ORG_PORTAL_URL=https://app.glimzo.ai
   EVENT_ID=your_event_id
   ORG_ID=your_org_id
   ```

2. Test SSO login from production organization portal

3. Verify HTTPS is working correctly

4. Check that tokens are being validated properly

5. Monitor logs for any errors

## Security Checklist

- [ ] HTTPS is enabled in production
- [ ] `ORG_PORTAL_URL` points to correct domain
- [ ] Event ID and Org ID are correctly configured
- [ ] Tokens expire after 5 minutes
- [ ] Failed login attempts are logged
- [ ] Regular login still works as fallback

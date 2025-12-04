# Laravel-Style Authentication Implementation

## Overview

This document describes the implementation of Laravel-style authentication patterns for InvoicePlane, following the problem statement requirements to apply Laravel authentication best practices while maintaining InvoicePlane's MD5/crypt password verification.

## Key Changes

### 1. SessionsController Refactoring

**Before:** The `login()` method handled both GET (display form) and POST (process login) requests in a single method with complex conditional logic.

**After:** Separated into two distinct methods:
- `login()` - Handles GET requests to display the login form
- `authenticate()` - Handles POST requests to process authentication

### 2. Laravel Authentication Patterns Applied

#### Request Validation
```php
$request->validate([
    'email' => ['required', 'string', 'email'],
    'password' => ['required', 'string'],
]);
```

#### Rate Limiting
Implemented using Laravel's `RateLimiter` facade:
- 5 failed attempts allowed before rate limiting
- Throttle key based on email + IP address
- Clear rate limiter on successful authentication
- Fire `Lockout` event when rate limited

#### Session Security
```php
// Regenerate session on successful login (prevent fixation attacks)
$request->session()->regenerate();

// On logout
$request->session()->flush();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

#### Error Handling
- Use `ValidationException` for authentication failures
- Provide user-friendly error messages
- Handle rate limiting with appropriate messaging

### 3. InvoicePlane-Specific Considerations

#### Password Verification
**Important:** Unlike Laravel which uses bcrypt, InvoicePlane uses MD5/crypt hashing. The implementation preserves this:

```php
// In SessionsService::auth()
if ((new Crypt())->check_password($user->user_password, $password)) {
    // Authentication successful
}
```

The `Crypt::check_password()` method uses PHP's `crypt()` function with the stored hash, which is compatible with InvoicePlane's existing password format.

#### User Active Check
Added verification that users must be active to authenticate:
```php
if (!$user->user_active) {
    return false;
}
```

### 4. Code Structure

#### SessionsController Methods

1. **login()**: Display login form (GET)
2. **authenticate()**: Process login (POST)
   - Validate input
   - Check rate limiting
   - Authenticate credentials
   - Clear rate limiter on success
   - Regenerate session
   - Redirect based on user type
3. **logout()**: Log out user (POST)
   - Flush session
   - Invalidate session
   - Regenerate CSRF token
4. **ensureIsNotRateLimited()**: Check rate limiting
5. **throttleKey()**: Generate unique throttle key

#### SessionsService Methods

1. **auth()**: Authenticate user
   - Find user by email
   - Check if user exists
   - Check if user is active
   - Verify password (MD5/crypt)
   - Set session data

### 5. Testing Infrastructure

#### UserFactory
Created a factory for the InvoicePlane User model (`Modules\Core\Database\Factories\UserFactory`) that:
- Uses `Crypt` library to generate proper password hashes
- Provides realistic test data
- Includes state methods: `admin()`, `guest()`, `inactive()`

#### Test Coverage
Added comprehensive tests in `SessionsControllerTest`:

1. **Authentication Success Tests**
   - Valid credentials authenticate successfully
   - Session data is set correctly
   - Redirects to correct dashboard based on user type
   - Session is regenerated

2. **Authentication Failure Tests**
   - Invalid password rejected
   - Non-existent user rejected
   - Inactive user rejected
   - Rate limiting after 5 failed attempts

3. **Validation Tests**
   - Email and password required
   - Email format validated

4. **Security Tests**
   - Session regeneration on login
   - Rate limiter cleared on success
   - Session invalidated on logout
   - CSRF token regenerated on logout

## Routes

The authentication system uses these routes:

```php
// Display login form
GET /sessions/login -> SessionsController@login

// Process authentication
POST /sessions/authenticate -> SessionsController@authenticate

// Logout
POST /sessions/logout -> SessionsController@logout
```

## Comparison with Laravel's LoginController

| Feature | Laravel | InvoicePlane Implementation |
|---------|---------|----------------------------|
| Request Validation | ✓ | ✓ |
| Rate Limiting | ✓ (RateLimiter) | ✓ (RateLimiter) |
| Session Regeneration | ✓ | ✓ |
| Lockout Events | ✓ | ✓ |
| Password Hashing | bcrypt/Hash | MD5/crypt |
| Auth Facade | ✓ | Custom SessionsService |
| Remember Me | ✓ | Not implemented |
| Email Verification | ✓ | Not implemented |

## Key Differences from Laravel

1. **Password Verification**: InvoicePlane uses `Crypt::check_password()` instead of Laravel's `Hash::check()`
2. **User Active Check**: Custom check for `user_active` field
3. **User Types**: Redirects based on `user_type` (1=admin, 2=guest)
4. **Session Data**: Stores custom session data (`user_type`, `user_id`, `user_name`, etc.)

## Security Improvements

1. **Session Fixation Prevention**: Session regenerated on successful login
2. **Rate Limiting**: Protects against brute force attacks
3. **CSRF Protection**: Token regenerated on logout
4. **Validation**: Input validated before processing
5. **User Active Check**: Inactive users cannot authenticate

## Testing

Run the authentication tests:
```bash
php artisan test --filter SessionsControllerTest
```

Expected results: 17 tests passing, covering:
- Login form display
- Successful authentication
- Failed authentication scenarios
- Rate limiting
- Session security
- Validation
- Logout functionality

## Future Enhancements

Potential improvements to consider:

1. **Two-Factor Authentication**: Add 2FA support
2. **Remember Me**: Implement persistent login
3. **Password History**: Track password changes
4. **Account Lockout**: Permanent lockout after X failures
5. **Login Audit Log**: Track all login attempts
6. **IP Whitelisting**: Allow/block specific IP ranges

## Backward Compatibility

The implementation maintains backward compatibility:
- Existing password hashes work without modification
- Session data structure unchanged
- User model fields unchanged
- Routes follow established patterns

## Migration Path from Old Code

The old code had these issues:
1. Mixed GET/POST handling in single method
2. No rate limiting
3. No session regeneration
4. Manual database queries instead of Eloquent
5. Commented-out authentication logic

All these issues have been resolved while maintaining the same functionality and improving security.

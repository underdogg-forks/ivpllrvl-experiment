# Security Policy

## Reporting a Vulnerability

Please report security issues to mail@invoiceplane.com

## Security Documentation

For comprehensive security information, please see:

- **[SECURITY-AUDIT-REPORT.md](SECURITY-AUDIT-REPORT.md)** - Complete security audit findings
- **[SECURITY-FIXES-APPLIED.md](SECURITY-FIXES-APPLIED.md)** - Details of all security fixes implemented
- **[SECURITY-TESTING-GUIDE.md](SECURITY-TESTING-GUIDE.md)** - Guide for testing security measures

## Recent Security Updates (November 2025)

All critical security vulnerabilities have been addressed:

### Fixed Vulnerabilities ✅

- **Path Traversal** (CRITICAL) - Fixed in file upload/download/delete operations
- **Insecure File Upload** (CRITICAL) - Added validation, size limits, type restrictions
- **Missing Authentication** (HIGH) - Auth middleware added to all upload endpoints
- **Open Redirect** (HIGH) - Fixed in SessionsController
- **Direct Superglobal Access** (HIGH) - Replaced with Laravel Request facade

### Security Status

- **Risk Level:** 🟢 LOW RISK (was 🔴 HIGH RISK)
- **Critical Vulnerabilities:** 0
- **High Vulnerabilities:** 0
- **Production Ready:** Yes

## Security Best Practices

When contributing to InvoicePlane:

1. **Never** use direct superglobal access (`$_POST`, `$_GET`, `$_SERVER`)
2. **Always** validate file uploads with both extension and MIME type checks
3. **Always** use `basename()` and `realpath()` for file operations
4. **Always** add authentication middleware to sensitive routes
5. **Never** commit secrets or credentials to the repository
6. **Always** use parameterized queries or Eloquent ORM
7. **Always** sanitize user input before output

## Responsible Disclosure

We appreciate responsible disclosure of security vulnerabilities. Please:

1. **Email** security details to mail@invoiceplane.com
2. **Wait** for acknowledgment before public disclosure
3. **Allow** reasonable time for fixes to be implemented
4. **Coordinate** public disclosure timeline with the team

Thank you for helping keep InvoicePlane secure!


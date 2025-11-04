# Security Testing Guide

This guide provides comprehensive instructions for testing the security fixes applied to InvoicePlane.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Path Traversal Testing](#path-traversal-testing)
3. [File Upload Security Testing](#file-upload-security-testing)
4. [Authentication Testing](#authentication-testing)
5. [CSRF Testing](#csrf-testing)
6. [Input Validation Testing](#input-validation-testing)
7. [Security Headers Testing](#security-headers-testing)
8. [Automated Security Scanning](#automated-security-scanning)

---

## Prerequisites

### Required Tools

```bash
# cURL for command-line testing
sudo apt-get install curl

# Optional: HTTPie for prettier output
pip install httpie

# Optional: OWASP ZAP for automated scanning
# Download from: https://www.zaproxy.org/download/
```

### Setup Test Environment

```bash
# Start the application
php artisan serve

# Or use Docker
docker-compose up
```

---

## Path Traversal Testing

### Test 1: File Download Path Traversal

**Expected:** All attempts should fail with 404 error

```bash
# Test 1.1: Basic path traversal
curl -i "http://localhost:8000/upload/get-file/../../../../etc/passwd"
# Expected: 404 Not Found

# Test 1.2: URL encoded path traversal
curl -i "http://localhost:8000/upload/get-file/..%2F..%2F..%2F..%2Fetc%2Fpasswd"
# Expected: 404 Not Found

# Test 1.3: Double encoded
curl -i "http://localhost:8000/upload/get-file/%252e%252e%252f%252e%252e%252f%252e%252e%252fetc%252fpasswd"
# Expected: 404 Not Found

# Test 1.4: Windows style path traversal
curl -i "http://localhost:8000/upload/get-file/..\\..\\..\\..\\windows\\system32\\config\\sam"
# Expected: 404 Not Found

# Test 1.5: Try to access .env file
curl -i "http://localhost:8000/upload/get-file/../../../.env"
# Expected: 404 Not Found
```

### Test 2: File Delete Path Traversal

**Expected:** Should require authentication first, then fail path validation

```bash
# Test 2.1: Try to delete system file via path traversal
curl -i -X POST "http://localhost:8000/upload/delete-file" \
  -H "Content-Type: application/json" \
  -d '{"url_key": "../../etc", "name": "passwd"}'
# Expected: 401 Unauthorized (no auth) or 410 Gone (path validation failed)

# Test 2.2: Try with encoded parameters
curl -i -X POST "http://localhost:8000/upload/delete-file" \
  -H "Content-Type: application/json" \
  -d '{"url_key": "..%2F..%2Fetc", "name": "passwd"}'
# Expected: 401 Unauthorized or 410 Gone
```

**✅ PASS Criteria:**
- All requests return 404 Not Found or 401 Unauthorized
- No system files are accessible
- No internal paths are revealed in error messages

---

## File Upload Security Testing

### Test 3: File Type Validation

**Expected:** Only whitelisted file types should be allowed

```bash
# Test 3.1: Try to upload PHP file (SHOULD FAIL)
echo '<?php echo "test"; ?>' > /tmp/shell.php
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/shell.php"
# Expected: 401 Unauthorized (no auth) or 415 Unsupported Media Type

# Test 3.2: Try to upload with double extension (SHOULD FAIL)
echo '<?php echo "test"; ?>' > /tmp/shell.php.jpg
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/shell.php.jpg"
# Expected: 401 Unauthorized or 415 Unsupported Media Type

# Test 3.3: Try to upload HTML file (SHOULD FAIL)
echo '<script>alert("XSS")</script>' > /tmp/malicious.html
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/malicious.html"
# Expected: 401 Unauthorized or 415 Unsupported Media Type

# Test 3.4: Upload valid PDF (SHOULD SUCCEED with auth)
# Note: Requires authentication token
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/valid.pdf"
# Expected: 200 OK (with auth) or 401 Unauthorized (without)
```

### Test 4: File Size Validation

```bash
# Test 4.1: Try to upload file larger than 10MB (SHOULD FAIL)
dd if=/dev/zero of=/tmp/large.pdf bs=1M count=11
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/large.pdf"
# Expected: 401 Unauthorized or 413 Payload Too Large

# Clean up
rm /tmp/large.pdf
```

### Test 5: Filename Sanitization

```bash
# Test 5.1: Special characters in filename
echo "test" > /tmp/"evil';rm -rf *.txt"
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/evil';rm -rf *.txt"
# Expected: Filename should be sanitized, no command execution

# Test 5.2: Unicode characters
echo "test" > /tmp/"файл.txt"
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/файл.txt"
# Expected: Filename converted to safe ASCII equivalent
```

**✅ PASS Criteria:**
- PHP, HTML, EXE files are rejected
- Files over 10MB are rejected
- Filenames are properly sanitized
- Only whitelisted extensions allowed: pdf, doc, docx, xls, xlsx, ppt, pptx, jpg, jpeg, png, gif, txt, csv, zip

---

## Authentication Testing

### Test 6: Unauthenticated Access

**Expected:** All protected endpoints should require authentication

```bash
# Test 6.1: Upload without authentication
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -F "file=@/tmp/test.pdf"
# Expected: 401 Unauthorized or redirect to login

# Test 6.2: Delete without authentication
curl -i -X POST "http://localhost:8000/upload/delete-file" \
  -d '{"url_key": "test", "name": "file.pdf"}'
# Expected: 401 Unauthorized or redirect to login

# Test 6.3: Show files without authentication
curl -i "http://localhost:8000/upload/show-files?url_key=test"
# Expected: 401 Unauthorized or redirect to login

# Test 6.4: Download without authentication
curl -i "http://localhost:8000/upload/get-file/test.pdf"
# Expected: 401 Unauthorized or redirect to login
```

**✅ PASS Criteria:**
- All upload operations require authentication
- Returns 401 Unauthorized or redirects to login
- No data is returned without valid session

---

## CSRF Testing

### Test 7: CSRF Protection

**Expected:** POST requests without CSRF token should fail

```bash
# Test 7.1: Upload without CSRF token
curl -i -X POST "http://localhost:8000/upload/upload-file" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -F "file=@/tmp/test.pdf"
# Expected: 419 Page Expired (CSRF token mismatch)

# Test 7.2: Delete without CSRF token
curl -i -X POST "http://localhost:8000/upload/delete-file" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d '{"url_key": "test", "name": "file.pdf"}'
# Expected: 419 Page Expired
```

**✅ PASS Criteria:**
- Requests without valid CSRF token are rejected
- Returns 419 status code
- State-changing operations use POST (not GET)

---

## Input Validation Testing

### Test 8: SQL Injection Attempts

```bash
# Test 8.1: SQL injection in url_key
curl -i "http://localhost:8000/upload/show-files?url_key=test' OR '1'='1"
# Expected: Sanitized or rejected, no SQL error

# Test 8.2: SQL injection in filename
curl -i -X POST "http://localhost:8000/upload/delete-file" \
  -d '{"url_key": "test", "name": "file.pdf\"; DROP TABLE uploads; --"}'
# Expected: Sanitized or rejected, no SQL execution
```

### Test 9: XSS Attempts

```bash
# Test 9.1: XSS in custom_date_format
curl -i -X POST "http://localhost:8000/some-endpoint" \
  -d 'custom_date_format=<script>alert("XSS")</script>&other_field=value'
# Expected: Input sanitized, script not executed
```

**✅ PASS Criteria:**
- SQL injection attempts are blocked
- XSS payloads are sanitized
- No error messages reveal database structure

---

## Security Headers Testing

### Test 10: HTTP Security Headers

```bash
# Test 10.1: Check for security headers
curl -I "http://localhost:8000"

# Expected headers:
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Content-Security-Policy: [should be present]
# Permissions-Policy: [should be present]
```

**Manual Check with Browser:**

```javascript
// Open browser console on the app
fetch(window.location.href)
  .then(response => {
    console.log('X-Frame-Options:', response.headers.get('X-Frame-Options'));
    console.log('X-Content-Type-Options:', response.headers.get('X-Content-Type-Options'));
    console.log('Content-Security-Policy:', response.headers.get('Content-Security-Policy'));
    console.log('Referrer-Policy:', response.headers.get('Referrer-Policy'));
  });
```

**✅ PASS Criteria:**
- All security headers are present
- X-Frame-Options is DENY
- X-Content-Type-Options is nosniff
- CSP is configured
- HSTS is enabled in production

---

## Automated Security Scanning

### Using OWASP ZAP

```bash
# Start ZAP in daemon mode
zap.sh -daemon -port 8090 -config api.disablekey=true

# Run automated scan
zap-cli --zap-url http://localhost:8090 quick-scan \
  --self-contained --start-options '-config api.disablekey=true' \
  http://localhost:8000

# Generate report
zap-cli --zap-url http://localhost:8090 report -o zap-report.html -f html
```

### Using Nikto

```bash
# Scan for common vulnerabilities
nikto -h http://localhost:8000
```

### Using PHPStan (Static Analysis)

```bash
# Run static analysis for security issues
composer require --dev phpstan/phpstan
vendor/bin/phpstan analyse --level=max Modules/Core/Controllers/UploadController.php
```

### Using Composer Audit

```bash
# Check for vulnerable dependencies
composer audit

# Expected: No known vulnerabilities
```

---

## Security Test Checklist

### Critical Tests

- [ ] Path traversal in file download blocked
- [ ] Path traversal in file delete blocked
- [ ] Path traversal in file upload blocked
- [ ] PHP file upload rejected
- [ ] HTML file upload rejected
- [ ] File size limit enforced (10MB)
- [ ] Authentication required on all upload endpoints
- [ ] CSRF protection on POST requests

### High Priority Tests

- [ ] Open redirect attempts blocked
- [ ] SQL injection attempts sanitized
- [ ] XSS attempts sanitized
- [ ] Direct superglobal access removed
- [ ] Filename sanitization working
- [ ] Extension whitelist enforced

### Medium Priority Tests

- [ ] Security headers present
- [ ] Error messages don't reveal paths
- [ ] No verbose debugging in production
- [ ] Session security configured
- [ ] Rate limiting on sensitive endpoints

---

## Expected Test Results Summary

| Test Category | Tests | Expected Pass Rate |
|--------------|-------|-------------------|
| Path Traversal | 7 tests | 100% |
| File Upload | 9 tests | 100% |
| Authentication | 4 tests | 100% |
| CSRF Protection | 2 tests | 100% |
| Input Validation | 3 tests | 100% |
| Security Headers | 1 test | 100% |

**Overall Expected:** 26/26 tests passing (100%)

---

## Reporting Security Issues

If you find a security vulnerability during testing:

1. **DO NOT** open a public GitHub issue
2. Email security@invoiceplane.com with:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Suggested fix (if any)

3. Wait for acknowledgment before public disclosure

---

## Continuous Security Testing

### Recommended Schedule

- **Daily:** Composer audit for dependency vulnerabilities
- **Weekly:** PHPStan static analysis
- **Monthly:** OWASP ZAP automated scan
- **Quarterly:** Professional penetration testing
- **Yearly:** Full security audit

### Automation

Add to CI/CD pipeline:

```yaml
# .github/workflows/security.yml
name: Security Tests

on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Composer Audit
        run: composer audit
      
      - name: PHPStan Security Analysis
        run: vendor/bin/phpstan analyse --level=max
      
      - name: Check for known vulnerabilities
        run: |
          # Add security scanning tools here
```

---

## Additional Resources

- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [File Upload Security](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)

---

**Last Updated:** November 4, 2025  
**Version:** 1.0

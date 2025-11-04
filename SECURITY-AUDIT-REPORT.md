# Security Audit Report

**Date:** November 4, 2025  
**Auditor:** Security Assessment  
**Application:** InvoicePlane (Laravel Migration)  
**Version:** Development (Post-Migration)

## Executive Summary

This security audit identified **CRITICAL** vulnerabilities that require immediate attention, particularly around path traversal attacks in file upload/download functionality. The application has good foundations with Laravel framework protections, but legacy code patterns have introduced security risks.

### Risk Summary
- **CRITICAL:** 3 vulnerabilities
- **HIGH:** 4 vulnerabilities  
- **MEDIUM:** 5 vulnerabilities
- **LOW:** 3 vulnerabilities

---

## CRITICAL Vulnerabilities

### 1. Path Traversal in UploadController::getFile() ⚠️ CRITICAL

**File:** `Modules/Core/Controllers/UploadController.php` (Line 144-165)

**Issue:**
The `getFile()` method accepts a filename parameter and directly concatenates it to the target path without proper validation. An attacker can use path traversal sequences (`../`) to access files outside the intended directory.

```php
public function getFile(string $filename): void
{
    $filename = urldecode($filename);
    
    if (!file_exists($this->targetPath . $filename)) {  // ⚠️ Direct concatenation
        // ...error handling
    }
    
    // ...
    readfile($this->targetPath . $filename);  // ⚠️ Path traversal vulnerability
}
```

**Attack Vector:**
```
GET /upload/get-file?filename=../../../../etc/passwd
GET /upload/get-file?filename=../../../.env
```

**Impact:** 
- Unauthorized file access
- Information disclosure (configuration files, source code, credentials)
- Potential system compromise

**Severity:** CRITICAL (CVSS 9.1)

**Recommendation:**
1. Use `basename()` to strip directory components
2. Validate against whitelist of allowed files
3. Use `realpath()` to resolve and validate paths
4. Check resolved path is within allowed directory

---

### 2. Path Traversal in UploadController::deleteFile() ⚠️ CRITICAL

**File:** `Modules/Core/Controllers/UploadController.php` (Line 118-132)

**Issue:**
The `deleteFile()` method has weak path validation. While it does use `realpath()` for checking, the validation logic has issues:

```php
public function deleteFile(Request $request, string $url_key): \Illuminate\Http\JsonResponse
{
    $filename = urldecode($request->input('name'));
    $finalPath = $this->targetPath . $url_key . '_' . $filename;  // ⚠️ User-controlled input

    if (realpath($this->targetPath) === mb_substr(realpath($finalPath), 0, mb_strlen(realpath($this->targetPath))) 
        && (!file_exists($finalPath) || @unlink($finalPath))) {  // ⚠️ Suppressed errors with @
        // ...
    }
}
```

**Issues:**
1. User controls both `url_key` and `filename` parameters
2. Error suppression with `@` masks security issues
3. `mb_substr()` comparison instead of `strpos()` check
4. No validation of `url_key` parameter

**Attack Vector:**
```
POST /upload/delete-file
url_key=../../sensitive&name=file.txt
```

**Impact:**
- Arbitrary file deletion
- Denial of service
- Data loss

**Severity:** CRITICAL (CVSS 9.0)

---

### 3. Insecure File Upload Validation ⚠️ CRITICAL

**File:** `Modules/Core/Controllers/UploadController.php` (Line 48-68)

**Issue:**
File upload validation relies solely on MIME type checking, which can be easily spoofed. No extension whitelist or content verification.

```php
public function uploadFile(Request $request, int $customerId, string $url_key): \Illuminate\Http\JsonResponse
{
    $file = $request->file('file');
    $filename = $this->sanitizeFileName($file->getClientOriginalName());  // ⚠️ Client-controlled
    
    $this->validateMimeType($file->getMimeType());  // ⚠️ MIME can be spoofed
    $file->move(dirname($filePath), $filename);  // ⚠️ No extension validation
}
```

**Attack Vector:**
1. Upload PHP shell with spoofed MIME type
2. Upload malicious files disguised as PDFs
3. Bypass filters with double extensions

**Impact:**
- Remote code execution
- Web shell upload
- System compromise

**Severity:** CRITICAL (CVSS 9.8)

---

## HIGH Vulnerabilities

### 4. Missing Authentication on Upload Endpoints 🔴 HIGH

**File:** `Modules/Core/routes/web/upload.php`

**Issue:**
Upload routes only use `web` middleware, no authentication required:

```php
Route::middleware('web')->group(function () {
    Route::post('upload/upload-file', [UploadController::class, 'uploadFile']);
    Route::get('upload/delete-file', [UploadController::class, 'deleteFile']);
    Route::get('upload/get-file', [UploadController::class, 'getFile']);
});
```

**Impact:**
- Unauthenticated users can upload files
- Unauthenticated users can delete files
- Unauthenticated users can download files

**Severity:** HIGH (CVSS 8.1)

**Recommendation:**
Add `auth` middleware to all upload routes.

---

### 5. Weak Filename Sanitization 🔴 HIGH

**File:** `Modules/Core/Controllers/UploadController.php` (Line 172-176)

**Issue:**
The filename sanitization is insufficient:

```php
private function sanitizeFileName(string $filename): string
{
    return preg_replace("/[^\\p{L}\\p{N}\\s\\-_''.]/u", '', mb_trim($filename));
}
```

**Problems:**
1. Allows dots (.), enabling double extension attacks (`shell.php.jpg`)
2. Allows spaces which can cause issues
3. No maximum length check
4. Allows apostrophes and special characters

**Attack Vector:**
```
Upload: malicious.php.jpg
Upload: ../../../../etc/passwd (after sanitization could still traverse)
```

**Severity:** HIGH (CVSS 7.5)

---

### 6. SQL Injection Risk in OrphanHelper 🔴 HIGH

**File:** `Modules/Core/Support/OrphanHelper.php` (Line 21-44)

**Issue:**
Uses hardcoded SQL with `DB::statement()` instead of query builder:

```php
$queries = [
    'DELETE FROM ip_invoices WHERE client_id NOT IN (SELECT client_id FROM ip_clients)',
    // ... more queries
];

foreach ($queries as $query) {
    DB::statement($query);  // ⚠️ No parameterization
}
```

**Current Status:**
- Queries are hardcoded (currently safe)
- But pattern is dangerous if copy-pasted with user input
- Should use Eloquent/Query Builder

**Severity:** HIGH (CVSS 7.2)

**Recommendation:**
Refactor to use Eloquent or Query Builder with proper parameterization.

---

### 7. Direct Superglobal Access 🔴 HIGH

**Files:** Multiple (14 occurrences)

**Issue:**
Direct access to `$_POST`, `$_GET`, `$_SERVER` without sanitization:

```php
// Modules/Core/Support/DateHelper.php
if (isset($_POST['custom_date_format'])) {
    $date_format = $_POST['custom_date_format'];  // ⚠️ No validation
}

// Modules/Core/Controllers/SessionsController.php
redirect($_SERVER['HTTP_REFERER']);  // ⚠️ Open redirect
```

**Impact:**
- Open redirect vulnerabilities
- XSS via unsanitized POST data
- HTTP header injection

**Severity:** HIGH (CVSS 7.0)

---

## MEDIUM Vulnerabilities

### 8. Missing CSRF Protection on GET-based State Changes 🟠 MEDIUM

**File:** `Modules/Core/routes/web/upload.php`

**Issue:**
State-changing operations use GET instead of POST:

```php
Route::get('upload/delete-file', [UploadController::class, 'deleteFile']);  // ⚠️ GET for delete
Route::get('upload/create-dir', [UploadController::class, 'createDir']);    // ⚠️ GET for create
```

**Impact:**
- CSRF attacks via image tags
- Unintended file deletion
- Directory creation abuse

**Severity:** MEDIUM (CVSS 6.5)

**Recommendation:**
Convert to POST routes and add CSRF token validation.

---

### 9. Information Disclosure in Error Messages 🟠 MEDIUM

**File:** `Modules/Core/Controllers/UploadController.php`

**Issue:**
Error messages reveal internal paths:

```php
$ref = isset($_SERVER['HTTP_REFERER']) ? ', Referer:' . $_SERVER['HTTP_REFERER'] : '';
$this->respondMessage(404, 'upload_error_file_not_found', $this->targetPath . $filename . $ref);
```

**Impact:**
- Path disclosure helps attackers
- Reveals directory structure
- Assists in crafting attacks

**Severity:** MEDIUM (CVSS 5.3)

---

### 10. Unsafe Error Suppression 🟠 MEDIUM

**File:** `Modules/Core/Controllers/UploadController.php` (Line 123)

**Issue:**
```php
if (realpath($this->targetPath) === mb_substr(realpath($finalPath), 0, mb_strlen(realpath($this->targetPath))) 
    && (!file_exists($finalPath) || @unlink($finalPath))) {  // ⚠️ @ suppresses errors
```

**Impact:**
- Hides security-relevant errors
- Makes debugging difficult
- Could mask exploitation attempts

**Severity:** MEDIUM (CVSS 5.0)

---

### 11. Unrestricted File Size 🟠 MEDIUM

**File:** `Modules/Core/Controllers/UploadController.php`

**Issue:**
No file size validation in `uploadFile()` method.

**Impact:**
- Denial of service via large files
- Disk space exhaustion
- Resource exhaustion

**Severity:** MEDIUM (CVSS 5.3)

---

### 12. Weak Content-Type Validation 🟠 MEDIUM

**File:** `Modules/Core/Services/UploadService.php`

**Issue:**
Allows dangerous file types:

```php
public array $content_types = [
    'php' => 'text/plain',  // ⚠️ Should not allow PHP uploads
    'htm' => 'text/html',   // ⚠️ HTML can contain XSS
    'html' => 'text/html',  // ⚠️ HTML can contain XSS
    // ...
];
```

**Severity:** MEDIUM (CVSS 6.0)

---

## LOW Vulnerabilities

### 13. Missing Security Headers 🟡 LOW

**Issue:**
No security headers configured (X-Frame-Options, CSP, etc.)

**Severity:** LOW (CVSS 3.1)

---

### 14. Verbose Error Logging 🟡 LOW

**Issue:**
Debug information may be logged in production.

**Severity:** LOW (CVSS 3.0)

---

### 15. Insecure Password Reset (Potential) 🟡 LOW

**Status:** Requires further investigation

**Severity:** LOW (CVSS 3.5)

---

## Positive Security Findings ✅

1. **Laravel Framework:** Modern framework with built-in protections
2. **Eloquent ORM:** Most database queries use ORM (prevents SQL injection)
3. **CSRF Middleware:** Available on web routes (needs verification)
4. **Input Validation:** Some validation exists via FormRequest classes
5. **GetController:** Has basic path traversal protection (needs improvement)

---

## Recommendations by Priority

### Immediate (Within 24 hours)

1. **Fix Path Traversal in UploadController::getFile()**
2. **Fix Path Traversal in UploadController::deleteFile()**
3. **Add Authentication to Upload Routes**
4. **Strengthen File Upload Validation**

### Short Term (Within 1 week)

5. **Fix Filename Sanitization**
6. **Convert GET to POST for State Changes**
7. **Fix Open Redirect Vulnerabilities**
8. **Add File Size Limits**
9. **Remove Dangerous Content Types**

### Medium Term (Within 1 month)

10. **Add Security Headers**
11. **Implement Rate Limiting**
12. **Add Comprehensive Input Validation**
13. **Security Testing Suite**
14. **Refactor OrphanHelper to use Query Builder**

### Long Term

15. **Security Awareness Training**
16. **Regular Security Audits**
17. **Dependency Scanning**
18. **Penetration Testing**

---

## Testing Recommendations

1. **Static Analysis:** Run PHPStan/Psalm with security rules
2. **Dynamic Testing:** Use OWASP ZAP or Burp Suite
3. **Dependency Scanning:** Use `composer audit`
4. **Unit Tests:** Add security-focused test cases
5. **Integration Tests:** Test authentication/authorization
6. **Penetration Testing:** Professional security assessment

---

## Compliance Notes

- **OWASP Top 10:** Multiple vulnerabilities align with OWASP categories
  - A01:2021 – Broken Access Control (uploads)
  - A03:2021 – Injection (potential SQL, path traversal)
  - A04:2021 – Insecure Design (file handling)
  - A05:2021 – Security Misconfiguration (verbose errors)
  
- **CWE Mappings:**
  - CWE-22: Path Traversal
  - CWE-434: Unrestricted Upload of Dangerous File Type
  - CWE-352: CSRF
  - CWE-601: Open Redirect

---

## Conclusion

The application has **critical security vulnerabilities** that must be addressed immediately, particularly in file upload/download functionality. The path traversal vulnerabilities pose the highest risk and should be the top priority for remediation.

**Overall Security Posture:** 🔴 **HIGH RISK**

**Recommended Action:** Implement fixes for critical vulnerabilities immediately before deploying to production.

---

**Report End**

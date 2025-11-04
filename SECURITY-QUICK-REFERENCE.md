# Security Quick Reference for Developers

A quick reference guide for writing secure code in InvoicePlane.

---

## ✅ DO's

### File Operations

```php
// ✅ GOOD: Use basename() to prevent path traversal
$safeFilename = basename($userInput);
$filePath = $uploadDir . $safeFilename;

// ✅ GOOD: Validate with realpath()
$resolvedPath = realpath($filePath);
$allowedPath = realpath($uploadDir);

if ($resolvedPath === false || strpos($resolvedPath, $allowedPath) !== 0) {
    abort(404);
}
```

### Input Validation

```php
// ✅ GOOD: Use Laravel's Request facade
$customFormat = request()->input('custom_date_format');

// ✅ GOOD: Use FormRequest for validation
public function store(QuoteRequest $request)
{
    $validated = $request->validated();
}

// ✅ GOOD: Whitelist allowed values
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$allowed = ['pdf', 'jpg', 'png', 'docx'];

if (!in_array($extension, $allowed, true)) {
    abort(415);
}
```

### Database Queries

```php
// ✅ GOOD: Use Eloquent ORM
$invoices = Invoice::where('client_id', $clientId)->get();

// ✅ GOOD: Use Query Builder with bindings
DB::table('invoices')
    ->where('client_id', $clientId)
    ->delete();
```

### Authentication

```php
// ✅ GOOD: Protect routes with auth middleware
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/upload/file', [UploadController::class, 'upload']);
});

// ✅ GOOD: Check permissions in controllers
if (!auth()->check()) {
    abort(401);
}
```

### File Uploads

```php
// ✅ GOOD: Validate file size
$maxSize = 10 * 1024 * 1024; // 10MB
if ($file->getSize() > $maxSize) {
    return response()->json(['error' => 'File too large'], 413);
}

// ✅ GOOD: Validate file type (both extension and MIME)
$extension = strtolower($file->getClientOriginalExtension());
$allowedExtensions = ['pdf', 'jpg', 'png', 'docx'];

if (!in_array($extension, $allowedExtensions, true)) {
    abort(415);
}

$mimeType = $file->getMimeType();
$allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];

if (!in_array($mimeType, $allowedMimes, true)) {
    abort(415);
}

// ✅ GOOD: Sanitize filename
$safeFilename = preg_replace("/[^a-zA-Z0-9_-]/", '_', pathinfo($filename, PATHINFO_FILENAME));
$safeFilename = mb_substr($safeFilename, 0, 200);
$extension = pathinfo($filename, PATHINFO_EXTENSION);
$filename = $safeFilename . '.' . $extension;
```

### Redirects

```php
// ✅ GOOD: Use Laravel's validated redirect
return redirect()->back();

// ✅ GOOD: Use named routes
return redirect()->route('dashboard');

// ✅ GOOD: Redirect to specific path
return redirect('/dashboard');
```

### Output Escaping

```php
<!-- ✅ GOOD: Escape output in views -->
<?php echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8'); ?>

<!-- ✅ GOOD: Use Laravel's e() helper -->
<?php echo e($userInput); ?>
```

---

## ❌ DON'Ts

### File Operations

```php
// ❌ BAD: Direct path concatenation
$filePath = $uploadDir . $userInput;  // VULNERABLE to path traversal

// ❌ BAD: No path validation
readfile($uploadDir . $_GET['file']);  // CRITICAL vulnerability

// ❌ BAD: Using user input directly
unlink($userProvidedPath);  // DANGEROUS
```

### Input Validation

```php
// ❌ BAD: Direct superglobal access
$value = $_POST['field'];  // Use request()->input() instead

// ❌ BAD: No validation
$email = $_GET['email'];  // Use request()->validate()

// ❌ BAD: Trusting client input
$isAdmin = $_POST['is_admin'];  // NEVER trust client-side data
```

### Database Queries

```php
// ❌ BAD: String concatenation in SQL
DB::statement("DELETE FROM users WHERE id = " . $userId);  // SQL injection

// ❌ BAD: Direct variable in query
$query = "SELECT * FROM users WHERE email = '$email'";  // SQL injection
```

### Authentication

```php
// ❌ BAD: No authentication check
public function deleteInvoice($id)
{
    Invoice::destroy($id);  // Anyone can delete!
}

// ❌ BAD: Routes without middleware
Route::post('/admin/delete-user', [UserController::class, 'delete']);
```

### File Uploads

```php
// ❌ BAD: No file type validation
$file->move($uploadDir, $file->getClientOriginalName());

// ❌ BAD: Only MIME type validation (can be spoofed)
if ($file->getMimeType() === 'image/jpeg') {
    // Not sufficient!
}

// ❌ BAD: Allowing dangerous extensions
$allowedTypes = ['php', 'html', 'exe'];  // NEVER!

// ❌ BAD: No file size limit
$file->store($uploadDir);  // DoS risk
```

### Redirects

```php
// ❌ BAD: Open redirect vulnerability
return redirect($_SERVER['HTTP_REFERER']);

// ❌ BAD: User-controlled redirect
return redirect($_GET['redirect_url']);
```

### Output

```php
<!-- ❌ BAD: Unescaped output -->
<?php echo $userInput; ?>  <!-- XSS vulnerability -->

<!-- ❌ BAD: Direct output -->
<div><?php echo $_POST['comment']; ?></div>  <!-- XSS vulnerability -->
```

### Error Handling

```php
// ❌ BAD: Suppressing errors
@unlink($file);  // Hides security issues

// ❌ BAD: Verbose error messages
catch (Exception $e) {
    echo $e->getMessage() . " in " . $e->getFile();  // Information disclosure
}
```

---

## Security Checklist for Code Reviews

### File Operations
- [ ] Uses `basename()` for user-provided filenames
- [ ] Uses `realpath()` for path validation
- [ ] Validates file is within allowed directory
- [ ] No direct path concatenation with user input

### File Uploads
- [ ] File size limit enforced
- [ ] Extension whitelist validation
- [ ] MIME type validation
- [ ] Filename sanitization
- [ ] No dangerous file types allowed (php, html, exe, etc.)

### Input Validation
- [ ] Uses Laravel Request facade (not superglobals)
- [ ] FormRequest used for complex validation
- [ ] Whitelist validation for enums/options
- [ ] Length limits on string inputs

### Authentication & Authorization
- [ ] Routes protected with auth middleware
- [ ] Permission checks in controllers
- [ ] CSRF protection on POST routes
- [ ] Session security configured

### Database
- [ ] Uses Eloquent ORM or Query Builder
- [ ] No raw SQL with user input
- [ ] Parameterized queries
- [ ] No DB::raw() with user input

### Output
- [ ] All user input escaped in views
- [ ] Uses `e()` or `htmlspecialchars()`
- [ ] JSON responses use `response()->json()`
- [ ] No information disclosure in errors

---

## Common Vulnerabilities Reference

| Vulnerability | Example | Fix |
|--------------|---------|-----|
| Path Traversal | `readfile($dir . $_GET['file'])` | Use `basename()` + `realpath()` |
| SQL Injection | `DB::raw("WHERE id = " . $id)` | Use Eloquent or bindings |
| XSS | `echo $_POST['comment']` | Use `e($comment)` |
| CSRF | GET request changes data | Use POST with CSRF token |
| Open Redirect | `redirect($_GET['url'])` | Use `redirect()->back()` |
| File Upload | No extension check | Whitelist + MIME + size check |
| Weak Passwords | No policy | Use validation rules |
| Session Fixation | No regeneration | Use `session()->regenerate()` |

---

## Security Tools

### Static Analysis
```bash
# PHPStan
vendor/bin/phpstan analyse --level=max

# Psalm
vendor/bin/psalm --show-info=true

# PHP Code Sniffer
vendor/bin/phpcs --standard=phpcs.xml
```

### Dependency Scanning
```bash
# Composer audit
composer audit

# Check for outdated packages
composer outdated --direct
```

### Dynamic Testing
```bash
# OWASP ZAP
zap-cli quick-scan http://localhost:8000

# Nikto
nikto -h http://localhost:8000
```

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [File Upload Security](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)

---

## Get Help

If you're unsure about security implications:

1. Check this guide
2. Review [SECURITY-AUDIT-REPORT.md](SECURITY-AUDIT-REPORT.md)
3. Ask in pull request review
4. Email security concerns to mail@invoiceplane.com

**Remember:** When in doubt, be more restrictive. It's easier to relax security than to fix vulnerabilities in production.

---

**Last Updated:** November 4, 2025  
**Version:** 1.0

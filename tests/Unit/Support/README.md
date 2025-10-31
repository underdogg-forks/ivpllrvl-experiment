# Support Helper Classes Test Suite

## Overview

This test suite provides comprehensive unit tests for the Support helper classes that were migrated from procedural helper functions to static class methods. The tests focus on pure functions and testable logic, ensuring the refactored code maintains the same behavior as the original implementation.

## Test Coverage Summary

### Files Tested (11 Helper Classes)

1. **NumberHelperTest.php** (259 lines, 28 tests)
   - Currency formatting with various settings
   - Amount and quantity formatting
   - Number standardization across different locales
   - Edge cases: null values, negative numbers, zero decimals

2. **DateHelperTest.php** (242 lines, 21 tests)
   - Date format conversions (MySQL ↔ User formats)
   - Multiple date format support (US, European, ISO)
   - Date validation
   - Date increment/decrement operations
   - Timestamp conversions

3. **SettingsHelperTest.php** (221 lines, 21 tests)
   - Setting retrieval with defaults
   - HTML escaping in settings
   - Gateway-specific settings filtering
   - Checkbox/select helper with multiple operators
   - Boolean and comparison operations

4. **MailerHelperTest.php** (194 lines, 19 tests)
   - Email configuration detection (phpmail, sendmail, SMTP)
   - Email address validation (single and multiple)
   - Various email formats and edge cases
   - TLD support (country codes, new TLDs)

5. **EchoHelperTest.php** (172 lines, 16 tests)
   - HTML special character escaping
   - XSS prevention
   - Null handling
   - Unicode character support
   - Output buffering tests

6. **TranslationHelperTest.php** (147 lines, 13 tests)
   - Translation key resolution
   - Default value fallback
   - Label wrapping with IDs
   - Locale management
   - Available languages detection

7. **DiacriticsHelperTest.php** (143 lines, 13 tests)
   - UTF-8 string detection
   - Accent removal from Latin characters
   - Unicode support (emoji, multibyte chars)
   - European character handling

8. **CustomValuesHelperTest.php** (122 lines, 11 tests)
   - Text formatting
   - Boolean value formatting
   - Null and empty value handling
   - Edge cases for custom field values

9. **UserHelperTest.php** (119 lines, 8 tests)
   - Username formatting
   - Company information display
   - Contact information handling
   - Name capitalization

10. **ClientHelperTest.php** (76 lines, 7 tests)
    - Gender formatting (male, female, other)
    - Multiple gender value types
    - Null and invalid value handling

11. **JsonErrorHelperTest.php** (57 lines, 3 tests)
    - POST data error collection
    - Array structure validation
    - Empty state handling

## Total Statistics

- **Total Lines of Test Code**: 1,752 lines
- **Total Test Methods**: 144 tests
- **Average Tests per Helper**: 13 tests
- **Code Coverage Focus**: Pure functions, data transformations, validation logic

## Testing Approach

### Key Principles

1. **Pure Function Testing**: Focus on methods with deterministic outputs given specific inputs
2. **Data Provider Pattern**: Extensive use of PHPUnit data providers for comprehensive scenario coverage
3. **Edge Case Coverage**: Null values, empty strings, invalid inputs, boundary conditions
4. **Setup/Teardown**: Proper database cleanup and state management
5. **Realistic Scenarios**: Tests reflect actual use cases from the application

### Test Categories

#### Happy Path Tests

- Valid inputs with expected outputs
- Standard use cases
- Common formatting scenarios

#### Edge Case Tests

- Null and empty values
- Invalid formats
- Boundary conditions
- Extreme values

#### Error Handling Tests

- Invalid data types
- Missing required data
- Unexpected input combinations

#### Integration Points

- Database interactions (Settings, Users)
- Laravel framework integration
- Locale and language handling

## Test Naming Convention

All tests follow the pattern: `it_<describes_behavior>`

Examples:
- `it_formats_currency_with_default_settings()`
- `it_validates_email_with_country_code_tld()`
- `it_returns_empty_string_for_null_input()`

## Running the Tests

```bash
# Run all Support helper tests
./vendor/bin/phpunit tests/Unit/Support/

# Run specific test file
./vendor/bin/phpunit tests/Unit/Support/NumberHelperTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage tests/Unit/Support/

# Run specific test method
./vendor/bin/phpunit --filter it_formats_currency_with_default_settings tests/Unit/Support/NumberHelperTest.php
```

## Dependencies

These tests rely on:
- **PHPUnit 11**: Modern test framework with PHP 8+ attributes
- **Laravel Framework**: Database, facades, settings management
- **Illuminate Database**: For DB interactions and cleanup
- **Core Models**: `Setting`, `User` models

## Test Data Management

### Database Cleanup

Tests that interact with the database use `setUp()` to clean tables:

```php
protected function setUp(): void
{
    parent::setUp();
    DB::table('ip_settings')->delete();
}
```

### Settings Management

Many tests create temporary settings:

```php
Setting::setValue('currency_symbol', '$');
Setting::setValue('decimal_point', '.');
```

### Isolation

Each test is isolated and doesn't depend on other tests' state.

## Coverage Gaps

The following helper classes were not fully tested due to external dependencies:

- **PdfHelper**: Requires PDF generation library integration
- **InvoiceHelper**: Heavy CodeIgniter dependencies
- **TemplateHelper**: Complex template parsing with external models
- **EInvoiceHelper**: XML generation and file system operations
- **DropzoneHelper**: Primarily outputs HTML, less suitable for unit testing
- **PagerHelper**: Requires CodeIgniter pagination context
- **OrphanHelper**: Database cleanup operations
- **PaymentsHelper**: ISO currency library dependency
- **RedirectHelper**: Session and redirect operations
- **MpdfHelper**: Not yet implemented
- **CountryHelper**: File system dependencies for country lists

These classes would benefit from integration tests or require additional refactoring to be more testable.

## Best Practices Demonstrated

1. **Descriptive Test Names**: Tests clearly describe what they verify
2. **Single Assertion Focus**: Most tests verify one behavior
3. **Data Providers**: Reduce code duplication for similar scenarios
4. **Proper Setup/Teardown**: Clean state between tests
5. **Type Safety**: Use of PHP type hints and strict comparisons
6. **Documentation**: Clear comments for complex scenarios
7. **Edge Case Coverage**: Comprehensive boundary testing
8. **Realistic Data**: Tests use realistic example data

## Future Enhancements

1. Add integration tests for PDF generation helpers
2. Mock external dependencies for untested helpers
3. Add performance benchmarks for formatting operations
4. Expand test coverage for edge cases
5. Add mutation testing to verify test effectiveness
6. Create factory methods for common test objects

## Maintenance Notes

- Update tests when helper methods change
- Add tests for new helper methods
- Keep test data realistic and varied
- Review and update edge cases periodically
- Maintain consistent naming conventions
#!/usr/bin/env php
<?php

/**
 * Controller Refactoring Helper Script
 *
 * This script analyzes a controller and provides refactoring suggestions
 * based on the STANDARDIZATION-GUIDE.md
 *
 * Usage: php refactor-helper.php <path-to-controller>
 */

if ($argc < 2) {
    echo "Usage: php refactor-helper.php <path-to-controller>\n";
    echo "Example: php refactor-helper.php Modules/Quotes/Controllers/QuotesController.php\n";
    exit(1);
}

$controllerPath = $argv[1];

if (!file_exists($controllerPath)) {
    echo "Error: File not found: $controllerPath\n";
    exit(1);
}

$content = file_get_contents($controllerPath);
$lines = file($controllerPath);

echo "\n=== Controller Refactoring Analysis ===\n\n";
echo "File: $controllerPath\n\n";

$issues = [];
$suggestions = [];

// Check 1: Class-level PHPDoc
if (!preg_match('/\/\*\*\s*\n\s*\*\s+\w+Controller/m', $content)) {
    $issues[] = "Missing or incomplete class-level PHPDoc";
    $suggestions[] = "Add comprehensive class-level PHPDoc with description and @legacy-file tag";
}

// Check 2: Method documentation
preg_match_all('/public function (\w+)\s*\(/', $content, $methods);
foreach ($methods[1] as $method) {
    // Look for PHPDoc before method
    $pattern = '/\/\*\*.*?public function ' . preg_quote($method) . '/s';
    if (!preg_match($pattern, $content)) {
        $issues[] = "Method '$method' missing PHPDoc";
    }
    
    // Check for @legacy-function tag
    $pattern = '/\/\*\*.*?@legacy-function.*?public function ' . preg_quote($method) . '/s';
    if (!preg_match($pattern, $content)) {
        $issues[] = "Method '$method' missing @legacy-function tag";
    }
}

// Check 3: Database queries in controller
if (preg_match('/DB::/', $content) || 
    preg_match('/->db->/', $content) ||
    preg_match('/\$this->load->/', $content)) {
    $issues[] = "Direct database queries or legacy CodeIgniter code found";
    $suggestions[] = "Move all database queries to service layer";
}

// Check 4: Inline validation
if (preg_match('/\$request->validate\(/', $content) || 
    preg_match('/\$this->validate\(/', $content)) {
    $issues[] = "Inline validation found";
    $suggestions[] = "Consider creating FormRequest class for validation";
}

// Check 5: AllowDynamicProperties
if (preg_match('/#\[AllowDynamicProperties\]/', $content)) {
    $issues[] = "Using AllowDynamicProperties attribute";
    $suggestions[] = "Remove AllowDynamicProperties and declare all properties";
}

// Check 6: Extends legacy controllers
if (preg_match('/extends (AdminController|BaseController|CI_Controller)/', $content)) {
    $issues[] = "Extending legacy base controller";
    $suggestions[] = "Remove extends from legacy controllers and use dependency injection";
}

// Check 7: Use statements organization
$useStatements = [];
preg_match_all('/^use (.+);$/m', $content, $useMatches);
$useStatements = $useMatches[1];
$sortedUseStatements = $useStatements;
sort($sortedUseStatements);
if ($useStatements !== $sortedUseStatements) {
    $issues[] = "Use statements not alphabetically sorted";
    $suggestions[] = "Sort use statements alphabetically";
}

// Report findings
echo "Issues Found: " . count($issues) . "\n";
echo str_repeat("=", 50) . "\n\n";

if (empty($issues)) {
    echo "✓ Controller appears to follow standards!\n\n";
} else {
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". ✗ $issue\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Suggestions:\n";
    echo str_repeat("=", 50) . "\n\n";
    
    foreach (array_unique($suggestions) as $i => $suggestion) {
        echo ($i + 1) . ". → $suggestion\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Methods in Controller:\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($methods[1] as $method) {
    // Find method signature
    $pattern = '/public function ' . preg_quote($method) . '\s*\(([^)]*)\)/';
    if (preg_match($pattern, $content, $match)) {
        echo "  • $method(" . trim($match[1]) . ")\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Recommended Next Steps:\n";
echo str_repeat("=", 50) . "\n\n";

echo "1. Review STANDARDIZATION-GUIDE.md for detailed patterns\n";
echo "2. Look at Modules/Projects/Controllers/TasksController.php as reference\n";
echo "3. Create FormRequest if validation exists\n";
echo "4. Move database queries to Service\n";
echo "5. Add comprehensive PHPDoc blocks\n";
echo "6. Create/update tests with #[CoversClass()] attribute\n";
echo "7. Run: composer dump-autoload && composer check\n";

echo "\n";

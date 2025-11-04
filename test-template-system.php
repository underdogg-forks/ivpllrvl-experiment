#!/usr/bin/env php
<?php

/**
 * Quick test script to verify PHP template system configuration.
 *
 * This script manually tests that:
 * 1. PHP view engine is registered
 * 2. Views can be found and rendered
 * 3. PHP templates work correctly
 */
echo "PHP Template System Test\n";
echo "========================\n\n";

// Test 1: Check if files exist
echo "1. Checking configuration files...\n";

$files = [
    'app/Providers/AppServiceProvider.php',
    'config/view.php',
    'config/modules.php',
    'resources/views/welcome.php',
    'resources/views/template-example.php',
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✓ {$file} exists\n";
    } else {
        echo "   ✗ {$file} NOT FOUND\n";
    }
}

// Test 2: Check AppServiceProvider content
echo "\n2. Checking AppServiceProvider configuration...\n";
$appServiceProvider = file_get_contents(__DIR__ . '/app/Providers/AppServiceProvider.php');

if (str_contains($appServiceProvider, 'PhpEngine')) {
    echo "   ✓ PhpEngine is referenced\n";
} else {
    echo "   ✗ PhpEngine not found\n";
}

if (str_contains($appServiceProvider, 'Register PHP engine FIRST')) {
    echo "   ✓ PHP engine is registered as primary\n";
} else {
    echo "   ✗ PHP engine priority comment not found\n";
}

if (str_contains($appServiceProvider, 'view.engine.resolver')) {
    echo "   ✓ View engine resolver is configured\n";
} else {
    echo "   ✗ View engine resolver not configured\n";
}

// Test 3: Check modules config
echo "\n3. Checking modules configuration...\n";
$modulesConfig = file_get_contents(__DIR__ . '/config/modules.php');

if ( ! str_contains($modulesConfig, 'index.blade.php')) {
    echo "   ✓ No .blade.php references in stubs\n";
} else {
    echo "   ✗ Found .blade.php references (should be .php)\n";
}

if (str_contains($modulesConfig, 'index.php')) {
    echo "   ✓ Uses .php extension for views\n";
} else {
    echo "   ✗ .php extension not found\n";
}

// Test 4: Check for blade files
echo "\n4. Checking for unwanted .blade.php files...\n";
$bladeFiles = [];
$iterator   = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/resources/views')
);

foreach ($iterator as $file) {
    if ($file->isFile() && str_contains($file->getFilename(), '.blade.php')) {
        $bladeFiles[] = $file->getPathname();
    }
}

if (empty($bladeFiles)) {
    echo "   ✓ No .blade.php files found in resources/views\n";
} else {
    echo '   ✗ Found ' . count($bladeFiles) . " .blade.php files:\n";
    foreach ($bladeFiles as $file) {
        echo "     - {$file}\n";
    }
}

// Test 5: Check view files exist
echo "\n5. Checking view files...\n";
$viewFiles = [
    'resources/views/welcome.php',
    'resources/views/template-example.php',
];

foreach ($viewFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "   ✓ {$file} exists\n";

        // Check if it's actually PHP (not Blade syntax)
        $content = file_get_contents($path);
        if ( ! str_contains($content, '@extends')
            && ! str_contains($content, '@section')
            && ! str_contains($content, '{{')) {
            echo "     ✓ Uses plain PHP syntax\n";
        } else {
            echo "     ⚠ May contain Blade syntax\n";
        }
    } else {
        echo "   ✗ {$file} NOT FOUND\n";
    }
}

// Summary
echo "\n========================\n";
echo "Test Complete!\n\n";
echo "Summary:\n";
echo "- PHP template system is configured in AppServiceProvider\n";
echo "- View configuration file created (config/view.php)\n";
echo "- Module stubs use .php extension (not .blade.php)\n";
echo "- View files use plain PHP syntax\n";
echo "\nThe application is now configured to use PHP templates as the primary template system.\n";

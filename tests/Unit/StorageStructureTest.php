<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test that storage directory structure matches Laravel requirements
 */
class StorageStructureTest extends TestCase
{
    /**
     * Test that required storage directories exist
     */
    public function test_required_storage_directories_exist(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        $requiredDirectories = [
            'storage/app',
            'storage/app/public',
            'storage/framework',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($requiredDirectories as $directory) {
            $fullPath = $basePath . '/' . $directory;
            $this->assertDirectoryExists(
                $fullPath,
                "Required storage directory does not exist: {$directory}"
            );
        }
    }

    /**
     * Test that storage directories are writable
     */
    public function test_storage_directories_are_writable(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        $writableDirectories = [
            'storage/app',
            'storage/app/public',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($writableDirectories as $directory) {
            $fullPath = $basePath . '/' . $directory;
            $this->assertTrue(
                is_writable($fullPath),
                "Storage directory is not writable: {$directory}"
            );
        }
    }

    /**
     * Test that .gitignore files exist in storage directories
     */
    public function test_gitignore_files_exist_in_storage_directories(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        $directoriesWithGitignore = [
            'storage/app',
            'storage/app/public',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($directoriesWithGitignore as $directory) {
            $gitignorePath = $basePath . '/' . $directory . '/.gitignore';
            $this->assertFileExists(
                $gitignorePath,
                ".gitignore file does not exist in: {$directory}"
            );
        }
    }

    /**
     * Test that .gitignore files have correct content
     */
    public function test_gitignore_files_have_correct_content(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        // Test storage/app/.gitignore
        $appGitignore = file_get_contents($basePath . '/storage/app/.gitignore');
        $this->assertStringContainsString('*', $appGitignore);
        $this->assertStringContainsString('!public/', $appGitignore);
        $this->assertStringContainsString('!.gitignore', $appGitignore);

        // Test storage/framework/cache/.gitignore
        $cacheGitignore = file_get_contents($basePath . '/storage/framework/cache/.gitignore');
        $this->assertStringContainsString('*', $cacheGitignore);
        $this->assertStringContainsString('!data/', $cacheGitignore);
        $this->assertStringContainsString('!.gitignore', $cacheGitignore);

        // Test other directories have standard .gitignore
        $standardGitignoreContent = "*\n!.gitignore\n";
        $standardDirs = [
            'storage/app/public',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
        ];

        foreach ($standardDirs as $directory) {
            $gitignorePath = $basePath . '/' . $directory . '/.gitignore';
            $content = file_get_contents($gitignorePath);
            $this->assertEquals(
                $standardGitignoreContent,
                $content,
                "Incorrect .gitignore content in: {$directory}"
            );
        }
    }
}

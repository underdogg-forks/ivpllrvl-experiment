<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\UploadController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * UploadController Feature Tests.
 *
 * Comprehensive test suite covering all routes and edge cases for file upload functionality.
 */
#[CoversClass(UploadController::class)]
class UploadControllerTest extends FeatureTestCase
{
    private string $testUploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testUploadDir = base_path('uploads/test');
        if (!is_dir($this->testUploadDir)) {
            mkdir($this->testUploadDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->testUploadDir)) {
            $files = glob($this->testUploadDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && file_exists($file)) {
                    unlink($file);
                }
            }
            if (file_exists($this->testUploadDir)) {
                rmdir($this->testUploadDir);
            }
        }
        parent::tearDown();
    }

    // ==================== ROUTE: POST /upload/upload-file ====================

    /**
     * Test file upload with valid file.
     */
    #[Group('crud')]
    #[Test]
    public function it_uploads_file_successfully(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

        /** Act */
        $this->actingAs($user);
        $response = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file,
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertJson([
            'message' => 'upload_file_uploaded_successfully',
        ]);
    }

    /**
     * Test file upload requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_upload(): void
    {
        /** Arrange */
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

        /** Act */
        $response = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file,
        ]);

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test file upload fails without file.
     */
    #[Group('validation')]
    #[Test]
    public function it_fails_upload_without_file(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), []);

        /** Assert */
        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'upload_error_no_file',
        ]);
    }

    /**
     * Test file upload rejects duplicate file.
     */
    #[Group('validation')]
    #[Test]
    public function it_rejects_duplicate_file_upload(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $file1 = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);
        $file2 = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100);

        /** Act */
        $this->actingAs($user);
        // Upload first file
        $response1 = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file1,
        ]);
        
        // Try to upload duplicate
        $response2 = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file2,
        ]);

        /** Assert */
        $response1->assertOk();
        $response2->assertStatus(409);
        $response2->assertJson([
            'message' => 'upload_error_duplicate_file',
        ]);
    }

    /**
     * Test file upload sanitizes filename.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_sanitizes_filename_on_upload(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('../../../etc/passwd', 100);

        /** Act */
        $this->actingAs($user);
        $response = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file,
        ]);

        /** Assert */
        // Filename should be sanitized, removing path traversal characters
        $response->assertOk();
        $data = $response->json();
        $this->assertStringNotContainsString('..', $data['filename']);
        $this->assertStringNotContainsString('/', $data['filename']);
    }

    /**
     * Test file upload with special characters in filename.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_special_characters_in_filename(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $file = \Illuminate\Http\UploadedFile::fake()->create('file<script>alert(1)</script>.pdf', 100);

        /** Act */
        $this->actingAs($user);
        $response = $this->post(route('upload.upload-file', [
            'customerId' => 1,
            'url_key' => 'test_key'
        ]), [
            'file' => $file,
        ]);

        /** Assert */
        $response->assertOk();
        $data = $response->json();
        // Should sanitize dangerous characters
        $this->assertStringNotContainsString('<', $data['filename']);
        $this->assertStringNotContainsString('>', $data['filename']);
    }

    // ==================== ROUTE: GET /upload/create-dir ====================

    /**
     * Test directory creation.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_directory_successfully(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $testDir = $this->testUploadDir . '/new_dir';

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.create-dir', ['path' => $testDir]));

        /** Assert */
        $response->assertOk();
        $this->assertTrue(is_dir($testDir));
        
        // Cleanup - use file_exists to avoid errors
        if (file_exists($testDir) && is_dir($testDir)) {
            rmdir($testDir);
        }
    }

    /**
     * Test directory creation handles existing directory.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_existing_directory(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $testDir = $this->testUploadDir . '/existing_dir';
        mkdir($testDir);

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.create-dir', ['path' => $testDir]));

        /** Assert */
        $response->assertOk();
        $this->assertTrue(is_dir($testDir));
        
        // Cleanup - use file_exists to avoid errors
        if (file_exists($testDir) && is_dir($testDir)) {
            rmdir($testDir);
        }
    }

    // ==================== ROUTE: GET /upload/show-files ====================

    /**
     * Test show files returns file list.
     */
    #[Group('smoke')]
    #[Test]
    public function it_shows_files_for_url_key(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.show-files', ['url_key' => 'test_key']));

        /** Assert */
        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    /**
     * Test show files returns empty array without url_key.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_returns_empty_array_without_url_key(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.show-files'));

        /** Assert */
        $response->assertOk();
        $response->assertJson([]);
    }

    /**
     * Test show files requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_show_files(): void
    {
        /** Act */
        $response = $this->get(route('upload.show-files', ['url_key' => 'test_key']));

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    // ==================== ROUTE: GET /upload/delete-file ====================

    /**
     * Test file deletion.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_file_successfully(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $filename = 'test_file.txt';
        $urlKey = 'test_key';
        
        // Create a test file
        $filePath = config('filesystems.cfiles_folder') . $urlKey . '_' . $filename;
        touch($filePath);

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.delete-file', [
            'url_key' => $urlKey,
            'name' => $filename,
        ]));

        /** Assert */
        $response->assertOk();
        $response->assertJson([
            'message' => 'upload_file_deleted_successfully',
        ]);
        
        // Cleanup - use file_exists to avoid errors
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Test file deletion fails for non-existent file.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_deletion_of_nonexistent_file(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.delete-file', [
            'url_key' => 'test_key',
            'name' => 'nonexistent.txt',
        ]));

        /** Assert */
        // Should handle gracefully
        $this->assertTrue(
            $response->isOk() || 
            $response->getStatusCode() == 410
        );
    }

    /**
     * Test file deletion prevents path traversal.
     */
    #[Group('validation')]
    #[Test]
    public function it_prevents_path_traversal_in_delete(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.delete-file', [
            'url_key' => 'test_key',
            'name' => '../../../etc/passwd',
        ]));

        /** Assert */
        $response->assertStatus(410);
        $response->assertJson([
            'message' => 'upload_error_file_delete',
        ]);
    }

    /**
     * Test file deletion requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_delete(): void
    {
        /** Act */
        $response = $this->get(route('upload.delete-file', [
            'url_key' => 'test_key',
            'name' => 'test.txt',
        ]));

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    // ==================== ROUTE: GET /upload/get-file ====================

    /**
     * Test file retrieval.
     */
    #[Group('smoke')]
    #[Test]
    public function it_retrieves_file_successfully(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $filename = 'test_file.txt';
        $filePath = config('filesystems.cfiles_folder') . $filename;
        
        // Create test file
        file_put_contents($filePath, 'test content');

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.get-file', ['filename' => $filename]));

        /** Assert */
        $response->assertOk();
        
        // Cleanup - use file_exists to avoid errors
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Test file retrieval returns 404 for non-existent file.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_returns_404_for_nonexistent_file(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.get-file', ['filename' => 'nonexistent.txt']));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test file retrieval prevents path traversal.
     */
    #[Group('validation')]
    #[Test]
    public function it_prevents_path_traversal_in_get_file(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('upload.get-file', ['filename' => '../../../etc/passwd']));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test file retrieval requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_get_file(): void
    {
        /** Act */
        $response = $this->get(route('upload.get-file', ['filename' => 'test.txt']));

        /** Assert */
        $response->assertRedirect(route('sessions.login'));
    }
}

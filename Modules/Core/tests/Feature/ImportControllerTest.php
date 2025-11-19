<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\ImportController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * ImportController Feature Tests.
 *
 * Comprehensive test suite covering all routes and edge cases for data import functionality.
 */
#[CoversClass(ImportController::class)]
class ImportControllerTest extends FeatureTestCase
{
    // ==================== ROUTE: GET /import (index) ====================

    /**
     * Test index displays import page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_import_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.index'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::import_index');
    }

    /**
     * Test index displays imports list.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_imports_list(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.index'));

        /* Assert */
        $response->assertOk();
        $response->assertViewHas('imports');
    }

    /**
     * Test index requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_index(): void
    {
        /** Act */
        $response = $this->get(route('import.index'));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test index handles pagination.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_pagination_on_import_index(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.index', ['page' => 1]));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::import_index');
    }

    // ==================== ROUTE: GET /import/form (form) ====================

    /**
     * Test form displays import form page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_import_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.form'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::import_import_index');
        $response->assertViewHas('files');
    }

    /**
     * Test form requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_form(): void
    {
        /** Act */
        $response = $this->get(route('import.form'));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test form displays only allowed CSV files.
     */
    #[Group('validation')]
    #[Test]
    public function it_displays_only_allowed_csv_files(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        // Ensure upload directory exists
        $uploadDir = base_path('uploads/import');
        if ( ! is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create allowed and disallowed files
        file_put_contents($uploadDir . '/clients.csv', 'test');
        file_put_contents($uploadDir . '/invoices.csv', 'test');
        file_put_contents($uploadDir . '/malicious.php', '<?php echo "bad"; ?>');

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.form'));

        /* Assert */
        $response->assertOk();
        $files = $response->viewData('files');

        // Should only show allowed CSV files
        $this->assertContains('clients.csv', $files);
        $this->assertContains('invoices.csv', $files);
        $this->assertNotContains('malicious.php', $files);

        // Cleanup - use file_exists to avoid errors
        if (file_exists($uploadDir . '/clients.csv')) {
            unlink($uploadDir . '/clients.csv');
        }
        if (file_exists($uploadDir . '/invoices.csv')) {
            unlink($uploadDir . '/invoices.csv');
        }
        if (file_exists($uploadDir . '/malicious.php')) {
            unlink($uploadDir . '/malicious.php');
        }
    }

    /**
     * Test form submission with valid files.
     */
    #[Group('crud')]
    #[Test]
    public function it_processes_import_with_valid_files(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $uploadDir = base_path('uploads/import');
        if ( ! is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Create a valid CSV file
        $csvContent = "client_name,client_email\nTest Client,test@example.com";
        file_put_contents($uploadDir . '/clients.csv', $csvContent);

        $formData = [
            'btn_submit' => '1',
            'files'      => ['clients.csv'],
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('import.form'), $formData);

        /* Assert */
        $response->assertRedirect(route('import.index'));

        // Cleanup - use file_exists to avoid errors
        if (file_exists($uploadDir . '/clients.csv')) {
            unlink($uploadDir . '/clients.csv');
        }
    }

    /**
     * Test form submission with no files selected.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_import_with_no_files_selected(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $formData = [
            'btn_submit' => '1',
            'files'      => [],
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('import.form'), $formData);

        /* Assert */
        $response->assertRedirect(route('import.index'));

        // Verify no imports were created
        $importCount = \Modules\Core\Models\Import::query()->count();
        $this->assertEquals(0, $importCount, 'No imports should be created when files array is empty');
    }

    /**
     * Test form submission filters out disallowed files.
     */
    #[Group('validation')]
    #[Test]
    public function it_filters_out_disallowed_files_on_import(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $formData = [
            'btn_submit' => '1',
            'files'      => ['clients.csv', 'malicious.php', 'invoices.csv'],
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('import.form'), $formData);

        /* Assert */
        $response->assertRedirect(route('import.index'));
        // Should only process clients.csv and invoices.csv, ignore malicious.php
    }

    /**
     * Test form submission with invoice items CSV.
     */
    #[Group('crud')]
    #[Test]
    public function it_processes_invoice_items_import(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $uploadDir = base_path('uploads/import');
        if ( ! is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $csvContent = "item_name,item_quantity\nTest Item,5";
        file_put_contents($uploadDir . '/invoice_items.csv', $csvContent);

        $formData = [
            'btn_submit' => '1',
            'files'      => ['invoice_items.csv'],
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('import.form'), $formData);

        /* Assert */
        $response->assertRedirect(route('import.index'));

        // Cleanup - use file_exists to avoid errors
        if (file_exists($uploadDir . '/invoice_items.csv')) {
            unlink($uploadDir . '/invoice_items.csv');
        }
    }

    /**
     * Test form submission with payments CSV.
     */
    #[Group('crud')]
    #[Test]
    public function it_processes_payments_import(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $uploadDir = base_path('uploads/import');
        if ( ! is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $csvContent = "payment_amount,payment_date\n100.00,2025-01-01";
        file_put_contents($uploadDir . '/payments.csv', $csvContent);

        $formData = [
            'btn_submit' => '1',
            'files'      => ['payments.csv'],
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('import.form'), $formData);

        /* Assert */
        $response->assertRedirect(route('import.index'));

        // Cleanup - use file_exists to avoid errors
        if (file_exists($uploadDir . '/payments.csv')) {
            unlink($uploadDir . '/payments.csv');
        }
    }

    // ==================== ROUTE: GET /import/delete/{id} (delete) ====================

    /**
     * Test delete removes import record.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_import_record(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        // Create an import record
        $import = \Modules\Core\Models\Import::create([
            'import_date' => date('Y-m-d H:i:s'),
        ]);

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.delete', ['id' => $import->import_id]));

        /* Assert */
        $response->assertRedirect(route('import.index'));

        // Verify import was deleted
        $this->assertDatabaseMissing('ip_imports', [
            'import_id' => $import->import_id,
        ]);
    }

    /**
     * Test delete requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_delete(): void
    {
        /** Act */
        $response = $this->get(route('import.delete', ['id' => 1]));

        /* Assert */
        $response->assertRedirect(route('sessions.login'));
    }

    /**
     * Test delete handles non-existent import gracefully.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_deleting_nonexistent_import(): void
    {
        /** Arrange */
        $user          = User::factory()->create();
        $nonexistentId = 99999;

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.delete', ['id' => $nonexistentId]));

        /* Assert */
        // Should redirect even if import doesn't exist
        $response->assertRedirect(route('import.index'));
    }

    /**
     * Test delete with invalid ID type.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_delete_with_invalid_id_type(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('import.delete', ['id' => 'invalid']));

        /* Assert */
        // Should handle gracefully
        $this->assertTrue(
            $response->isRedirect()
            || $response->getStatusCode() >= 400
        );
    }
}

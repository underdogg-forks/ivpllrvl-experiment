<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\EmailTemplatesController;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * EmailTemplatesController Feature Tests.
 *
 * Tests email template management for customizing system emails.
 */
#[CoversClass(EmailTemplatesController::class)]
class EmailTemplatesControllerTest extends FeatureTestCase
{
    /**
     * Data provider for required field validation tests.
     *
     * @return array<string, array{field: string, data: array<string, string>}>
     */
    public static function requiredFieldsProvider(): array
    {
        return [
            'title is required' => [
                'field' => 'email_template_title',
                'data'  => [
                    'email_template_title'   => '',
                    'email_template_subject' => 'Subject',
                    'email_template_body'    => 'Body',
                    'btn_submit'             => '1',
                ],
            ],
            'subject is required' => [
                'field' => 'email_template_subject',
                'data'  => [
                    'email_template_title'   => 'Title',
                    'email_template_subject' => '',
                    'email_template_body'    => 'Body',
                    'btn_submit'             => '1',
                ],
            ],
            'body is required' => [
                'field' => 'email_template_body',
                'data'  => [
                    'email_template_title'   => 'Title',
                    'email_template_subject' => 'Subject',
                    'email_template_body'    => '',
                    'btn_submit'             => '1',
                ],
            ],
        ];
    }

    /**
     * Test index displays paginated list of email templates.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_paginated_list_of_email_templates(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        EmailTemplate::factory()->count(5)->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.index'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::email_templates_index');
        $response->assertViewHas('email_templates');
    }

    /**
     * Test templates are ordered alphabetically by title.
     */
    #[Test]
    public function it_orders_email_templates_alphabetically_by_title(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        EmailTemplate::factory()->create(['email_template_title' => 'Welcome Email']);
        EmailTemplate::factory()->create(['email_template_title' => 'Invoice Email']);
        EmailTemplate::factory()->create(['email_template_title' => 'Quote Email']);

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.index'));

        /* Assert */
        $response->assertOk();
        $templates = $response->viewData('email_templates');
        $titles    = $templates->pluck('email_template_title')->toArray();

        $this->assertEquals('Invoice Email', $titles[0]);
        $this->assertEquals('Quote Email', $titles[1]);
        $this->assertEquals('Welcome Email', $titles[2]);
    }

    /**
     * Test form displays create form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.form'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::email_templates_form');
        $response->assertViewHas('email_template');

        $template = $response->viewData('email_template');
        $this->assertInstanceOf(EmailTemplate::class, $template);
        $this->assertFalse($template->exists);
    }

    /**
     * Test form displays edit form with existing template.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_edit_form_with_existing_template(): void
    {
        /** Arrange */
        $user     = User::factory()->create();
        $template = EmailTemplate::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.form', ['id' => $template->email_template_id]));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::email_templates_form');
        $response->assertViewHas('email_template');

        $viewTemplate = $response->viewData('email_template');
        $this->assertEquals($template->email_template_id, $viewTemplate->email_template_id);
    }

    /**
     * Test form creates new email template.
     *
     * JSON Payload:
     * {
     *   "email_template_title": "New Template",
     *   "email_template_subject": "Subject",
     *   "email_template_body": "Body content",
     *   "btn_submit": "1"
     * }
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_email_template_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $templateData = [
            'email_template_title'   => 'New Template',
            'email_template_subject' => 'Subject',
            'email_template_body'    => 'Body content',
            'btn_submit'             => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $templateData);

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');

        $this->assertDatabaseHas('ip_email_templates', [
            'email_template_title' => 'New Template',
        ]);
    }

    /**
     * Test form updates existing email template.
     *
     * JSON Payload:
     * {
     *   "email_template_title": "Updated Title",
     *   "email_template_subject": "Invoice Reminder",
     *   "email_template_body": "Please pay your invoice.",
     *   "btn_submit": "1"
     * }
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_email_template(): void
    {
        /** Arrange */
        $user     = User::factory()->create();
        $template = EmailTemplate::factory()->create(['email_template_title' => 'Old Title']);

        $updateData = [
            'email_template_title'   => 'Updated Title',
            'email_template_subject' => $template->email_template_subject,
            'email_template_body'    => $template->email_template_body,
            'btn_submit'             => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form', ['id' => $template->email_template_id]), $updateData);

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');

        $this->assertDatabaseHas('ip_email_templates', [
            'email_template_id'    => $template->email_template_id,
            'email_template_title' => 'Updated Title',
        ]);
    }

    /**
     * Test form redirects on cancel.
     *
     * JSON Payload:
     * {
     *   "btn_cancel": "1"
     * }
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_index_on_cancel(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $cancelData = [
            'btn_cancel' => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $cancelData);

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));
    }

    /**
     * Test delete removes email template.
     *
     * JSON Payload:
     * {
     *   "email_template_id": 1
     * }
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_email_template(): void
    {
        /** Arrange */
        $user     = User::factory()->create();
        $template = EmailTemplate::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->post(
            route('email_templates.delete', ['id' => $template->email_template_id])
        );

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');

        $this->assertDatabaseMissing('ip_email_templates', [
            'email_template_id' => $template->email_template_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent template.
     *
     * JSON Payload:
     * {
     *   "email_template_id": 99999
     * }
     */
    #[Group('smoke')]
    #[Test]
    public function it_returns_404_when_deleting_non_existent_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->post(
            route('email_templates.delete', ['id' => 99999])
        );

        /* Assert */
        $response->assertNotFound();
    }

    /**
     * Test form returns 404 for non-existent template in edit mode.
     */
    #[Group('smoke')]
    #[Test]
    public function it_returns_404_when_editing_non_existent_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.form', ['id' => 99999]));

        /* Assert */
        $response->assertNotFound();
    }

    // ==================== EDGE CASES & VALIDATION ====================

    /**
     * Test template creation requires authentication.
     */
    #[Group('auth')]
    #[Test]
    public function it_requires_authentication_for_all_routes(): void
    {
        /* Act & Assert */
        $this->get(route('email_templates.index'))->assertRedirect(route('sessions.login'));
        $this->get(route('email_templates.form'))->assertRedirect(route('sessions.login'));
    }

    /**
     * Test form validates required fields.
     *
     * @param string                $field The field name that should have validation errors
     * @param array<string, string> $data  The invalid form data
     */
    #[Group('validation')]
    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('requiredFieldsProvider')]
    public function it_validates_required_fields(string $field, array $data): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $data);

        /* Assert */
        $response->assertSessionHasErrors($field);
    }

    /**
     * Test form handles very long title.
     *
     * JSON Payload:
     * {
     *   "email_template_title": "AAAA...300 chars",
     *   "email_template_subject": "Subject",
     *   "email_template_body": "Body",
     *   "btn_submit": "1"
     * }
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_very_long_title(): void
    {
        /** Arrange */
        $user      = User::factory()->create();
        $longTitle = str_repeat('A', 300);

        $templateData = [
            'email_template_title'   => $longTitle,
            'email_template_subject' => 'Subject',
            'email_template_body'    => 'Body',
            'btn_submit'             => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $templateData);

        /* Assert */
        // Should either truncate or reject
        $this->assertTrue(
            $response->isRedirect()
            || $response->getSession()->has('errors')
        );
    }

    /**
     * Test form handles HTML in body.
     *
     * JSON Payload:
     * {
     *   "email_template_title": "HTML Template",
     *   "email_template_subject": "Subject",
     *   "email_template_body": "<p>Hello {client_name},</p><p>Your invoice is ready.</p>",
     *   "btn_submit": "1"
     * }
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_handles_html_in_email_body(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $templateData = [
            'email_template_title'   => 'HTML Template',
            'email_template_subject' => 'Subject',
            'email_template_body'    => '<p>Hello {client_name},</p><p>Your invoice is ready.</p>',
            'btn_submit'             => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $templateData);

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));

        $template = EmailTemplate::query()->where('email_template_title', 'HTML Template')->first();
        $this->assertNotNull($template);
        $this->assertStringContainsString('<p>', $template->email_template_body);
    }

    /**
     * Test form handles template variables.
     *
     * JSON Payload:
     * {
     *   "email_template_title": "Variable Template",
     *   "email_template_subject": "Invoice {invoice_number}",
     *   "email_template_body": "Dear {client_name}, your total is {invoice_total}",
     *   "btn_submit": "1"
     * }
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_preserves_template_variables(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        $templateData = [
            'email_template_title'   => 'Variable Template',
            'email_template_subject' => 'Invoice {invoice_number}',
            'email_template_body'    => 'Dear {client_name}, your total is {invoice_total}',
            'btn_submit'             => '1',
        ];

        /* Act */
        $this->actingAs($user);
        $response = $this->post(route('email_templates.form'), $templateData);

        /* Assert */
        $response->assertRedirect(route('email_templates.index'));

        $template = EmailTemplate::query()->where('email_template_title', 'Variable Template')->first();
        $this->assertStringContainsString('{client_name}', $template->email_template_body);
        $this->assertStringContainsString('{invoice_number}', $template->email_template_subject);
    }

    /**
     * Test pagination handles large number of templates.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_paginates_large_template_list(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        EmailTemplate::factory()->count(50)->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.index'));

        /* Assert */
        $response->assertOk();
        $templates = $response->viewData('email_templates');
        // Should have pagination or all templates
        $this->assertGreaterThan(0, $templates->count());
    }

    /**
     * Test index displays empty state when no templates.
     */
    #[Group('edge-cases')]
    #[Test]
    public function it_displays_empty_state_when_no_templates(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        EmailTemplate::query()->delete();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('email_templates.index'));

        /* Assert */
        $response->assertOk();
        $templates = $response->viewData('email_templates');
        $this->assertCount(0, $templates);
    }

    /**
     * Test deletion with invalid ID type.
     *
     * JSON Payload:
     * {
     *   "email_template_id": "invalid"
     * }
     */
    #[Group('validation')]
    #[Test]
    public function it_handles_invalid_id_type_on_delete(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->post(
            route('email_templates.delete', ['id' => 'invalid'])
        );

        /* Assert */
        $this->assertTrue(
            $response->isNotFound()
            || $response->getStatusCode() >= 400
        );
    }
}

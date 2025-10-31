<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\EmailTemplatesController;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
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
     * Test index displays paginated list of email templates.
     */
    #[Test]
    public function it_displays_paginated_list_of_email_templates(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        EmailTemplate::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('email_templates.index'));

        /** Assert */
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

        /** Act */
        $response = $this->actingAs($user)->get(route('email_templates.index'));

        /** Assert */
        $response->assertOk();
        $templates = $response->viewData('email_templates');
        $titles = $templates->pluck('email_template_title')->toArray();
        
        $this->assertEquals('Invoice Email', $titles[0]);
        $this->assertEquals('Quote Email', $titles[1]);
        $this->assertEquals('Welcome Email', $titles[2]);
    }

    /**
     * Test form displays create form.
     */
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('email_templates.form'));

        /** Assert */
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
    #[Test]
    public function it_displays_edit_form_with_existing_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $template = EmailTemplate::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('email_templates.form', ['id' => $template->email_template_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::email_templates_form');
        $response->assertViewHas('email_template');
        
        $viewTemplate = $response->viewData('email_template');
        $this->assertEquals($template->email_template_id, $viewTemplate->email_template_id);
    }

    /**
     * Test form creates new email template.
     */
    #[Test]
    public function it_creates_new_email_template_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{email_template_title: string, email_template_subject: string, email_template_body: string, btn_submit: string} $templateData */
        $templateData = [
            'email_template_title' => 'New Template',
            'email_template_subject' => 'Subject',
            'email_template_body' => 'Body content',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('email_templates.form'), $templateData);

        /** Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_email_templates', [
            'email_template_title' => 'New Template',
        ]);
    }

    /**
     * Test form updates existing email template.
     */
    #[Test]
    public function it_updates_existing_email_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $template = EmailTemplate::factory()->create(['email_template_title' => 'Old Title']);
        
        /** @var array{email_template_title: string, email_template_subject: string, email_template_body: string, btn_submit: string} $updateData */
        $updateData = [
            'email_template_title' => 'Updated Title',
            'email_template_subject' => $template->email_template_subject,
            'email_template_body' => $template->email_template_body,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('email_templates.form', ['id' => $template->email_template_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_email_templates', [
            'email_template_id' => $template->email_template_id,
            'email_template_title' => 'Updated Title',
        ]);
    }

    /**
     * Test form redirects on cancel.
     */
    #[Test]
    public function it_redirects_to_index_on_cancel(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{btn_cancel: string} $cancelData */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('email_templates.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('email_templates.index'));
    }

    /**
     * Test delete removes email template.
     */
    #[Test]
    public function it_deletes_email_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $template = EmailTemplate::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => $template->email_template_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('email_templates.delete', $deleteParams));

        /** Assert */
        $response->assertRedirect(route('email_templates.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_email_templates', [
            'email_template_id' => $template->email_template_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent template.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /** @var array{id: int} $deleteParams */
        $deleteParams = [
            'id' => 99999,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('email_templates.delete', $deleteParams));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test form returns 404 for non-existent template in edit mode.
     */
    #[Test]
    public function it_returns_404_when_editing_non_existent_template(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('email_templates.form', ['id' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}

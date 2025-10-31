<?php

namespace Modules\Core\Services;

/**
 * EmailTemplateService.
 *
 * Service class for managing email template business logic
 */
class EmailTemplateService
{
    /**
     * Get validation rules for email templates.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'email_template_title'   => 'required|string|max:255',
            'email_template_subject' => 'required|string|max:255',
            'email_template_body'    => 'required|string',
        ];
    }
}

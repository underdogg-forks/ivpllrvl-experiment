<?php

namespace Modules\Core\Services;

use Modules\Core\Models\EmailTemplate;

/**
 * EmailTemplateService.
 *
 * Service class for managing email template business logic
 */
class EmailTemplateService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return EmailTemplate::class;
    }
}

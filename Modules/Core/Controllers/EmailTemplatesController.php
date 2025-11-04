<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\EmailTemplateService;

use Modules\Core\Support\TranslationHelper;
/**
 * EmailTemplatesController
 *
 * Manages email template CRUD operations for system notifications
 *
 * @legacy-file application/modules/email_templates/controllers/Email_templates.php
 */
class EmailTemplatesController
*/
class EmailTemplatesController
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService
        protected EmailTemplateService $emailTemplateService
    ) {
    }

    /**
     * Display a paginated list of email templates.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-function index
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $templates = EmailTemplate::query()
            ->orderBy('email_template_title')
            ->paginate(15, ['*'], 'page', $page);

        return view('core::email_templates_index', ['email_templates' => $templates]);
    }

    /**
     * Display form for creating or editing an email template.
     *
     * @param int|null $id Email template ID (null for create)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     *
     * @legacy-function form
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     */
    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('email_templates.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate([
                'email_template_title' => 'required|string|max:255',
                'email_template_subject' => 'required|string|max:255',
                'email_template_body' => 'required|string',
                'email_template_from_name' => 'nullable|string|max:255',
                'email_template_from_email' => 'nullable|email|max:255',
                'email_template_cc' => 'nullable|string|max:255',
                'email_template_bcc' => 'nullable|string|max:255',
            ]);

            if ($id) {
                $this->emailTemplateService->update($id, $validated);
            } else {
                $this->emailTemplateService->create($validated);
            }

            return redirect()->route('email_templates.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $template = $id ? $this->emailTemplateService->find($id) : new EmailTemplate();
        if ($id && !$template) {
            abort(404);
        }

        return view('core::email_templates_form', ['email_template' => $template]);
    }

    /**
     * Delete an email template.
     *
     * @param int $id Email template ID
     *
     * @return \Illuminate\Http\RedirectResponse
     *
     * @legacy-function delete
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->emailTemplateService->delete($id);

        return redirect()->route('email_templates.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}

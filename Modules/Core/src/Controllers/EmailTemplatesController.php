<?php

namespace Modules\Core\Controllers;

use Illuminate\Http\RedirectResponse;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Services\EmailTemplateService;
use Modules\Core\Support\TranslationHelper;

/**
 * EmailTemplatesController.
 *
 * Manages email template CRUD operations for system notifications
 *
 * @legacy-file application/modules/email_templates/controllers/Email_templates.php
 */
class EmailTemplatesController
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService
    ) {}

    /**
     * Display a paginated list of email templates.
     *
     * @param int $page Page number for pagination
     *
     * @return \Illuminate\View\View
     *
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     *
     * @legacy-function index
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
     * @return \Illuminate\View\View|RedirectResponse
     *
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     *
     * @legacy-function form
     */
    public function form(?int $id = null): \Illuminate\View\View|RedirectResponse
    {
        if (request()->post('btn_cancel')) {
            return redirect()->route('email_templates.index');
        }

        if (request()->isMethod('post') && request()->post('btn_submit')) {
            /**
             * if ($this->input->post('is_update') == 0 && $this->input->post('email_template_title') != '') {
             * $check = $this->db->get_where('ip_email_templates', ['email_template_title' => $this->input->post('email_template_title')])->result();
             * if ( ! empty($check)) {
             * $this->session->set_flashdata('alert_error', trans('email_template_already_exists'));
             * redirect('email_templates/form');
             * }
             * }.
             */
            $validated = request()->validate([
                'email_template_title'      => 'required|string|max:255',
                'email_template_subject'    => 'required|string|max:255',
                'email_template_body'       => 'required|string',
                'email_template_from_name'  => 'nullable|string|max:255',
                'email_template_from_email' => 'nullable|email|max:255',
                'email_template_cc'         => 'nullable|string|max:255',
                'email_template_bcc'        => 'nullable|string|max:255',
            ]);

            if ($id) {
                $this->emailTemplateService->update($id, $validated);
            } else {
                $this->emailTemplateService->create($validated);
            }

            return redirect()->route('email_templates.index')->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        $template = $id ? $this->emailTemplateService->find($id) : new EmailTemplate();
        if ($id && ! $template) {
            abort(404);
        }

        /*
         * $this->layout->set([
         * 'custom_fields'         => $custom_fields,
         * 'invoice_templates'     => $this->template->get_invoice_templates(),
         * 'quote_templates'       => $this->template->get_quote_templates(),
         * 'selected_pdf_template' => $this->emailtemplates->form_value('email_template_pdf_template'),
         * ]);
         */

        return view('core::email_templates_form', ['email_template' => $template]);
    }

    /**
     * Delete an email template.
     *
     * @param int $id Email template ID
     *
     * @return RedirectResponse
     *
     * @legacy-file application/modules/email_templates/controllers/Email_templates.php
     *
     * @legacy-function delete
     */
    public function delete(int $id): RedirectResponse
    {
        $this->emailTemplateService->delete($id);

        return redirect()->route('email_templates.index')->with('alert_success', TranslationHelper::trans('record_successfully_deleted'));
    }
}

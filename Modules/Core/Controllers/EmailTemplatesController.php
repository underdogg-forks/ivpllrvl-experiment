<?php

namespace Modules\Core\Controllers;

use Modules\Core\Models\EmailTemplate;

class EmailTemplatesController
{
    /** @legacy-file application/modules/email_templates/controllers/Email_templates.php */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $templates = EmailTemplate::query()->orderBy('email_template_title')->paginate(15, ['*'], 'page', $page);
        return view('core::email_templates_index', ['email_templates' => $templates]);
    }

    public function form(?int $id = null)
    {
        if (request()->post('btn_cancel')) return redirect()->route('email_templates.index');
        
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            $validated = request()->validate(EmailTemplate::validationRules());
            if ($id) {
                EmailTemplate::query()->findOrFail($id)->update($validated);
            } else {
                EmailTemplate::query()->create($validated);
            }
            return redirect()->route('email_templates.index')->with('alert_success', trans('record_successfully_saved'));
        }

        $template = $id ? EmailTemplate::query()->findOrFail($id) : new EmailTemplate();
        return view('core::email_templates_form', ['email_template' => $template]);
    }

    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        EmailTemplate::query()->findOrFail($id)->delete();
        return redirect()->route('email_templates.index')->with('alert_success', trans('record_successfully_deleted'));
    }
}

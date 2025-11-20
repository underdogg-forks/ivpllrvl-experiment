<?php

namespace Modules\Invoices\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Support\TranslationHelper;
use Modules\Core\Traits\HandlesDeletion;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Services\InvoiceGroupService;

/**
 * InvoiceGroupsController.
 *
 * Manages invoice groups which control invoice numbering patterns
 */
class InvoiceGroupsController
{
    use HandlesDeletion;

    /**
     * InvoiceGroup service instance.
     *
     * @param InvoiceGroupService $invoiceGroupService
     */
    public function __construct(
        protected InvoiceGroupService $invoiceGroupService
    ) {}

    /**
     * Display a paginated list of invoice groups.
     *
     * @param int $page Page number for pagination
     *
     * @return View
     *
     * @legacy-function index
     *
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     *
     * @legacy-line 32
     */
    public function index(int $page = 0): View
    {
        $invoiceGroups = InvoiceGroup::ordered()
            ->paginate(15, ['*'], 'page', $page);

        return view('invoices::invoice_groups_index', [
            'invoice_groups' => $invoiceGroups,
        ]);
    }

    /**
     * Display form for creating or editing an invoice group.
     *
     * @param int|null $id Invoice group ID (null for create)
     *
     * @return View|RedirectResponse
     *
     * @legacy-function form
     *
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     *
     * @legacy-line 42
     */
    public function form(?int $id = null): View|RedirectResponse
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('invoice_groups.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $rules     = $this->invoiceGroupService->getValidationRules();
            $validated = request()->validate($rules);

            if ($id) {
                // Update existing
                $this->invoiceGroupService->update($id, $validated);
            } else {
                // Create new
                $this->invoiceGroupService->create($validated);
            }

            return redirect()->route('invoice_groups.index')
                ->with('alert_success', TranslationHelper::trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $invoiceGroup = $this->invoiceGroupService->find($id);
            if ( ! $invoiceGroup) {
                abort(404);
            }
        } else {
            // Set defaults for new record
            $invoiceGroup = new InvoiceGroup([
                'invoice_group_left_pad' => 0,
                'invoice_group_next_id'  => 1,
            ]);
        }

        return view('invoices::invoice_groups_form', [
            'invoice_group' => $invoiceGroup,
        ]);
    }

    /**
     * Delete an invoice group.
     *
     * @param int $id Invoice group ID
     *
     * @return RedirectResponse
     *
     * @legacy-function delete
     *
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     *
     * @legacy-line 71
     */
    public function delete(int $id): RedirectResponse
    {
        // Early return for validation
        if ($id <= 0) {
            return $this->redirectWithError('invoice_groups.index', TranslationHelper::trans('invalid_invoice_group_id'));
        }

        // Check if invoice group exists
        $invoiceGroup = $this->invoiceGroupService->find($id);
        if ( ! $invoiceGroup) {
            return $this->redirectWithError('invoice_groups.index', TranslationHelper::trans('invoice_group_not_found'));
        }

        // Business rule: Cannot delete invoice groups with related invoices or quotes
        if ( ! $this->invoiceGroupService->canDelete($id)) {
            $blockers = $this->invoiceGroupService->getDeletionBlockers($id);
            $message  = TranslationHelper::trans('invoice_group_deletion_not_allowed', [
                'invoices' => $blockers['invoices'],
                'quotes'   => $blockers['quotes'],
            ]);

            return $this->redirectWithError('invoice_groups.index', $message);
        }

        // Execute deletion with standardized error handling
        return $this->executeDelete(
            fn () => $this->invoiceGroupService->delete($id),
            'invoice_groups.index'
        );
    }
}

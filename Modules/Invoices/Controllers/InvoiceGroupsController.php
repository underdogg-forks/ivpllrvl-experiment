<?php

namespace Modules\Invoices\Http\Controllers;

use Modules\Invoices\Entities\InvoiceGroup;
use Illuminate\Http\Request;

/**
 * InvoiceGroupsController
 * 
 * Manages invoice groups which control invoice numbering patterns
 */
class InvoiceGroupsController
{
    /**
     * Display a paginated list of invoice groups
     * 
     * @param int $page Page number for pagination
     * @return \Illuminate\View\View
     * 
     * @legacy-function index
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     * @legacy-line 32
     */
    public function index(int $page = 0): \Illuminate\View\View
    {
        $invoiceGroups = InvoiceGroup::ordered()
            ->paginate(15, ['*'], 'page', $page);
        
        return view('invoices::invoice_groups_index', [
            'invoice_groups' => $invoiceGroups,
        ]);
    }

    /**
     * Display form for creating or editing an invoice group
     * 
     * @param int|null $id Invoice group ID (null for create)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * 
     * @legacy-function form
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     * @legacy-line 42
     */
    public function form(?int $id = null)
    {
        // Handle cancel button
        if (request()->post('btn_cancel')) {
            return redirect()->route('invoice_groups.index');
        }

        // Handle form submission
        if (request()->isMethod('post') && request()->post('btn_submit')) {
            // Validate input
            $rules = InvoiceGroup::validationRules();
            $validated = request()->validate($rules);
            
            if ($id) {
                // Update existing
                $invoiceGroup = InvoiceGroup::findOrFail($id);
                $invoiceGroup->update($validated);
            } else {
                // Create new
                InvoiceGroup::create($validated);
            }
            
            return redirect()->route('invoice_groups.index')
                ->with('alert_success', trans('record_successfully_saved'));
        }

        // Load existing record for editing
        if ($id) {
            $invoiceGroup = InvoiceGroup::find($id);
            if (!$invoiceGroup) {
                abort(404);
            }
        } else {
            // Set defaults for new record
            $invoiceGroup = new InvoiceGroup([
                'invoice_group_left_pad' => 0,
                'invoice_group_next_id' => 1,
            ]);
        }

        return view('invoices::invoice_groups_form', [
            'invoice_group' => $invoiceGroup,
        ]);
    }

    /**
     * Delete an invoice group
     * 
     * @param int $id Invoice group ID
     * @return \Illuminate\Http\RedirectResponse
     * 
     * @legacy-function delete
     * @legacy-file application/modules/invoice_groups/controllers/Invoice_groups.php
     * @legacy-line 71
     */
    public function delete(int $id): \Illuminate\Http\RedirectResponse
    {
        $invoiceGroup = InvoiceGroup::findOrFail($id);
        $invoiceGroup->delete();
        
        return redirect()->route('invoice_groups.index')
            ->with('alert_success', trans('record_successfully_deleted'));
    }

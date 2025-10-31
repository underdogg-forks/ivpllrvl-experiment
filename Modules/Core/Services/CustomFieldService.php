<?php

namespace Modules\Core\Services;

/**
 * CustomFieldService.
 *
 * Service class for managing custom field business logic
 */
class CustomFieldService
{
    /**
     * Get custom tables list.
     *
     * @return array
     */
    public function getCustomTables(): array
    {
        return [
            'ip_client_custom'  => trans('clients'),
            'ip_invoice_custom' => trans('invoices'),
            'ip_payment_custom' => trans('payments'),
            'ip_quote_custom'   => trans('quotes'),
            'ip_user_custom'    => trans('users'),
        ];
    }

    /**
     * Get custom field types.
     *
     * @return array
     */
    public function getCustomTypes(): array
    {
        return [
            'text'         => trans('text_input'),
            'textarea'     => trans('textarea'),
            'checkbox'     => trans('checkbox'),
            'date'         => trans('date'),
            'select'       => trans('dropdown'),
        ];
    }

    /**
     * Get nice name for form element.
     *
     * @param string $element
     *
     * @return string
     */
    public function getNicename(string $element): string
    {
        $nicenames = [
            'ip_client_custom'  => 'client',
            'ip_invoice_custom' => 'invoice',
            'ip_payment_custom' => 'payment',
            'ip_quote_custom'   => 'quote',
            'ip_user_custom'    => 'user',
        ];

        return $nicenames[$element] ?? '';
    }

    /**
     * Get validation rules for custom fields.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'custom_field_table' => 'required|string',
            'custom_field_label' => 'required|string|max:255',
            'custom_field_type'  => 'required|string',
        ];
    }
}

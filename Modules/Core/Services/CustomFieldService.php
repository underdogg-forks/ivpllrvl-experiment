<?php

namespace Modules\Core\Services;

use Modules\Core\Services\BaseService;
use Modules\Core\Models\CustomField;

/**
 * CustomFieldService.
 *
 * Service class for managing custom field business logic
 */
class CustomFieldService extends BaseService
{
    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return CustomField::class;
    }

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
     * Get custom fields by table name.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTable(string $tableName): \Illuminate\Database\Eloquent\Collection
    {
        return CustomField::query()->where('custom_field_table', $tableName)->get();
    }

    /**
     * Get custom fields by table name ordered by custom_field_order.
     *
     * @param string $tableName
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByTableOrdered(string $tableName): \Illuminate\Database\Eloquent\Collection
    {
        return CustomField::query()
            ->where('custom_field_table', $tableName)
            ->orderBy('custom_field_order')
            ->get();
    }

    /**
     * Check if custom fields exist for a table.
     *
     * @param string $tableName
     *
     * @return bool
     */
    public function existsForTable(string $tableName): bool
    {
        return CustomField::query()->where('custom_field_table', $tableName)->exists();
    }
}

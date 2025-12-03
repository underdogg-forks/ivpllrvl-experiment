<?php

namespace Modules\Invoices\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupService extends BaseService
{
    public function getValidationRules(): array
    {
        return [
            'invoice_group_name'              => 'required|string|max:255',
            'invoice_group_identifier_format' => 'required|string',
            'invoice_group_next_id'           => 'required|integer|min:1',
            'invoice_group_left_pad'          => 'required|integer|min:0',
        ];
    }

    /**
     * @param      $invoice_group_id
     * @param bool $set_next
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoice_groups/models/Mdl_invoice_group.php
     *
     * @legacy-function generate_invoice_number()
     */
    public function generateInvoiceNumber(InvoiceGroup $invoiceGroup, bool $setNext = true): string
    {
        $identifier = $this->parseIdentifierFormat(
            $invoiceGroup->invoice_group_identifier_format,
            $invoiceGroup->invoice_group_next_id,
            $invoiceGroup->invoice_group_left_pad
        );

        if ($setNext) {
            $this->setNextInvoiceNumber($invoiceGroup);
        }

        return $identifier;
    }

    /**
     * Get all invoice groups.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return InvoiceGroup::all();
    }

    /**
     * Check if invoice group can be deleted.
     *
     * @param int $id Invoice group ID
     *
     * @return bool True if invoice group can be deleted
     */
    public function canDelete(int $id): bool
    {
        $blockers = $this->getDeletionBlockers($id);

        return $blockers['invoices'] === 0 && $blockers['quotes'] === 0;
    }

    /**
     * Get deletion blockers for invoice group.
     *
     * @param int $id Invoice group ID
     *
     * @return array Array of blocker counts
     */
    public function getDeletionBlockers(int $id): array
    {
        return [
            'invoices' => \Modules\Invoices\Models\Invoice::query()
                ->where('invoice_group_id', $id)
                ->count(),
            'quotes' => \Modules\Quotes\Models\Quote::query()
                ->where('invoice_group_id', $id)
                ->count(),
        ];
    }

    protected function getModelClass(): string
    {
        return InvoiceGroup::class;
    }

    /**
     * @param $invoice_group_id
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoice_groups/models/Mdl_invoice_group.php
     *
     * @legacy-function set_next_invoice_number()
     */
    private function setNextInvoiceNumber(InvoiceGroup $invoiceGroup): void
    {
        $invoiceGroup->increment('invoice_group_next_id');
    }

    /**
     * @param $identifier_format
     * @param $next_id
     * @param $left_pad
     *
     * @return mixed
     *
     * Legacy migration info:
     *
     * @legacy-file application/modules/invoice_groups/models/Mdl_invoice_group.php
     *
     * @legacy-function parse_identifier_format()
     */
    private function parseIdentifierFormat(string $identifierFormat, string $nextId, int $leftPad): string
    {
        if (preg_match_all('/{{{([^{|}]*)}}}/', $identifierFormat, $templateVars)) {
            foreach ($templateVars[1] as $var) {
                $replace = match ($var) {
                    'year'  => date('Y'),
                    'yy'    => date('y'),
                    'month' => date('m'),
                    'day'   => date('d'),
                    'id'    => mb_str_pad($nextId, $leftPad, '0', STR_PAD_LEFT),
                    default => '',
                };

                $identifierFormat = str_replace('{{{' . $var . '}}}', $replace, $identifierFormat);
            }
        }

        return $identifierFormat;
    }
}

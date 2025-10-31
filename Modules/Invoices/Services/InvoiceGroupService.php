<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Models\InvoiceGroup;

class InvoiceGroupService
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

    private function setNextInvoiceNumber(InvoiceGroup $invoiceGroup): void
    {
        $invoiceGroup->increment('invoice_group_next_id');
    }

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

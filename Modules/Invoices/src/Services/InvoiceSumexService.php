<?php

namespace Modules\Invoices\Services;

class InvoiceSumexService
{
    public function getValidationRules(): array
    {
        return [
            'sumex_invoice'        => 'required|integer',
            'sumex_reason'         => 'nullable|integer',
            'sumex_diagnosis'      => 'nullable|string',
            'sumex_observations'   => 'nullable|string',
            'sumex_treatmentstart' => 'nullable|date',
            'sumex_treatmentend'   => 'nullable|date',
            'sumex_casedate'       => 'nullable|date',
            'sumex_casenumber'     => 'nullable|string',
        ];
    }
}

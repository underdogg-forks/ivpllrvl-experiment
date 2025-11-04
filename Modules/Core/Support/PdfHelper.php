<?php

namespace Modules\Core\Support;

class PdfHelper
{
    /**
     * Print global discount in PDF output.
     *
     * @origin Modules/Core/Helpers/pdf_helper.php
     *
     * @param object $obj                 Invoice or quote object
     * @param bool   $show_item_discounts Whether item discounts are shown
     * @param string $is                  Type of document ('invoice' or 'quote')
     */
    public static function discount_global_print_in_pdf($obj, $show_item_discounts, string $is = 'invoice'): void
    {
        $type = [
            'p' => $is . '_discount_percent',
            'a' => $is . '_discount_amount',
        ];

        $discount = 0;
        if ($obj->{$type['p']} !== '0.00') {
            $discount = format_amount($obj->{$type['p']}) . '%';
        } elseif ($obj->{$type['a']} !== '0.00') {
            $discount = format_currency($obj->{$type['a']});
        }

        if ($discount) {
            ?>
            <tr>
                <td class="text-right" colspan="<?php echo $show_item_discounts ? '5' : '4'; ?>">
                    <?php echo mb_rtrim(trans('discount'), ' '); ?>
                </td>
                <td class="text-right"><?php echo $discount; ?></td>
            </tr>
            <?php
        }
    }

    /**
     * Generate PDF for an invoice.
     *
     * @origin Modules/Core/Helpers/pdf_helper.php
     *
     * @param string      $invoice_id       Invoice ID
     * @param bool        $stream           Whether to stream the PDF
     * @param string|null $invoice_template Template to use
     * @param bool|null   $is_guest         Whether viewing as guest
     *
     * @return string|null PDF content or filename
     */
    public static function generate_invoice_pdf($invoice_id, $stream = true, $invoice_template = null, $is_guest = null)
    {
        $invoiceService        = app(\Modules\Invoices\Services\InvoiceService::class);
        $paymentMethodService  = app(\Modules\Payments\Services\PaymentMethodService::class);
        $invoiceItemService    = app(\Modules\Invoices\Services\InvoiceItemService::class);
        $invoiceTaxRateService = app(\Modules\Invoices\Services\InvoiceTaxRateService::class);

        $invoice = $invoiceService->findWithRelations($invoice_id, ['client', 'user']);

        if ( ! $invoice) {
            return;
        }

        // Get invoice with payments - TODO: move to service method
        $invoice = $invoiceService->findWithRelations($invoice_id, ['payments']);

        set_language($invoice->client_language);

        if ( ! $invoice_template) {
            $invoice_template = \Modules\Core\Support\TemplateHelper::select_pdf_invoice_template($invoice);
        }

        $payment_method = null;
        if ((int) $invoice->payment_method !== 0) {
            $payment_method = $paymentMethodService->findByMethodId($invoice->payment_method);
        }

        $items = $invoiceItemService->getByInvoiceId($invoice_id);

        $show_item_discounts = false;
        foreach ($items as $item) {
            if ($item->item_discount !== '0.00') {
                $show_item_discounts = true;
                break;
            }
        }

        $custom_fields = [
            'invoice' => static::getCustomFieldValues('ip_invoice_custom', $invoice->invoice_id),
            'client'  => static::getCustomFieldValues('ip_client_custom', $invoice->client_id),
            'user'    => static::getCustomFieldValues('ip_user_custom', $invoice->user_id),
        ];

        if ($invoice->quote_id) {
            $custom_fields['quote'] = static::getCustomFieldValues('ip_quote_custom', $invoice->quote_id);
        }

        $filename = trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number);

        $xml_id          = false;
        $embed_xml       = false;
        $associatedFiles = null;

        if (get_setting('einvoicing')) {
            $einvoice  = \Modules\Core\Support\EInvoiceHelper::get_einvoice_usage($invoice, $items, false);
            $xml_id    = $einvoice->user ? $einvoice->name : false;
            $options   = [];
            $generator = $xml_id;
            $path      = APPPATH . 'helpers/XMLconfigs/';

            if ($xml_id && file_exists($path . $xml_id . '.php')) {
                $xml_setting = [];
                include $path . $xml_id . '.php';

                $embed_xml = $xml_setting['embedXML'];
                $XMLname   = $xml_setting['XMLname'];
                $options   = empty($xml_setting['options']) ? $options : $xml_setting['options'];
                $generator = empty($xml_setting['generator']) ? $generator : $xml_setting['generator'];
            }

            if ($xml_id && $embed_xml) {
                $associatedFiles = [[
                    'path'           => generate_xml_invoice_file($invoice, $items, $generator, $filename, $options),
                    'name'           => $_SERVER['CIIname'] ?? $XMLname,
                    'mime'           => $_SERVER['CIImime'] ?? 'text/xml',
                    'description'    => $xml_id . ' e-' . trans('invoice'),
                    'AFRelationship' => 'Alternative',
                ]];
            }
        }

        $data = [
            'invoice'             => $invoice,
            'invoice_tax_rates'   => $invoiceTaxRateService->getByInvoiceId($invoice_id),
            'items'               => $items,
            'payment_method'      => $payment_method,
            'output_type'         => 'pdf',
            'show_item_discounts' => $show_item_discounts,
            'custom_fields'       => $custom_fields,
            'legacy_calculation'  => config_item('legacy_calculation'),
        ];

        $html = view('invoice_templates/pdf/' . $invoice_template, $data)->render();

        $retval = pdf_create(
            html:             $html,
            filename:         $filename,
            stream:           $stream,
            password:         $invoice->invoice_password,
            isInvoice:        true,
            is_guest:         $is_guest,
            embed_xml:        $embed_xml,
            associated_files: $associatedFiles
        );

        if ($embed_xml && file_exists(UPLOADS_TEMP_FOLDER . $filename . '.xml')) {
            if (defined('IP_DEBUG') && IP_DEBUG) {
                copy(UPLOADS_TEMP_FOLDER . $filename . '.xml', UPLOADS_TEMP_FOLDER . 'einvoice_test.xml');
            }
            unlink(UPLOADS_TEMP_FOLDER . $filename . '.xml');
        }

        if ($xml_id && $embed_xml !== true) {
            if ( ! empty($options['CIIname'])) {
                $_SERVER['CIIname'] = $options['CIIname'];
            }
            $filename = date('Y-m-d') . '_' . $filename;
            generate_xml_invoice_file($invoice, $items, $generator, $filename, $options);
        }

        return $retval;
    }

    /**
     * Generate SUMEX compliant invoice PDF.
     *
     * @origin Modules/Core/Helpers/pdf_helper.php
     *
     * @param string      $invoice_id       Invoice ID
     * @param bool        $stream           Whether to stream the PDF
     * @param string|null $invoice_template Template to use
     * @param bool        $client           Whether called from client context
     *
     * @return string|null PDF content or filename
     */
    public static function generate_invoice_sumex($invoice_id, $stream = true, $invoice_template = null, $client = false)
    {
        $invoice = \Modules\Invoices\Models\Invoice::find($invoice_id);

        if ( ! $invoice) {
            return;
        }

        $invoiceItemService = app(\Modules\Invoices\Services\InvoiceItemService::class);
        $items              = $invoiceItemService->getByInvoiceId($invoice_id);

        $sumex = new \Modules\Core\Libraries\Sumex([
            'invoice' => $invoice,
            'items'   => $items,
        ]);

        $sumexPDF = $sumex->pdf($invoice_template);
        $sha1sum  = sha1($sumexPDF);
        $shortsum = mb_substr($sha1sum, 0, 8);
        $filename = trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number) . '_' . $shortsum;

        if ( ! $client) {
            $temp = tempnam('/tmp', 'invsumex_');
            file_put_contents($temp, $sumexPDF);

            $pdf       = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($temp);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size       = $pdf->getTemplateSize($templateId);
                $pdf->addPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            unlink($temp);

            if ($stream) {
                header('Content-Type: application/pdf');
                $pdf->Output($filename . '.pdf', 'I');

                return;
            }

            $filePath = UPLOADS_TEMP_FOLDER . $filename . '.pdf';
            $pdf->Output($filePath, 'F');

            return $filePath;
        }

        if ($stream) {
            return $sumexPDF;
        }

        $filePath = UPLOADS_TEMP_FOLDER . $filename . '.pdf';
        file_put_contents($filePath, $sumexPDF);

        return $filePath;
    }

    /**
     * Generate PDF for a quote.
     *
     * @origin Modules/Core/Helpers/pdf_helper.php
     *
     * @param string      $quote_id       Quote ID
     * @param bool        $stream         Whether to stream the PDF
     * @param string|null $quote_template Template to use
     *
     * @return string|null PDF content or filename
     */
    public static function generate_quote_pdf($quote_id, $stream = true, $quote_template = null)
    {
        $quoteService        = app(\Modules\Quotes\Services\QuoteService::class);
        $quoteItemService    = app(\Modules\Quotes\Services\QuoteItemService::class);
        $quoteTaxRateService = app(\Modules\Quotes\Services\QuoteTaxRateService::class);

        $quote = $quoteService->findWithRelations($quote_id, ['client', 'user']);

        if ( ! $quote) {
            return;
        }

        set_language($quote->client_language);

        if ( ! $quote_template) {
            $quote_template = get_setting('pdf_quote_template');
        }

        $items = $quoteItemService->getByQuoteId($quote_id);

        $show_item_discounts = false;
        foreach ($items as $item) {
            if ($item->item_discount !== '0.00') {
                $show_item_discounts = true;
                break;
            }
        }

        $custom_fields = [
            'quote'  => static::getCustomFieldValues('ip_quote_custom', $quote->quote_id),
            'client' => static::getCustomFieldValues('ip_client_custom', $quote->client_id),
            'user'   => static::getCustomFieldValues('ip_user_custom', $quote->user_id),
        ];

        if (get_setting('einvoicing')) {
            \Modules\Core\Support\EInvoiceHelper::get_einvoice_usage($quote, $items, false);
        }

        $data = [
            'quote'               => $quote,
            'quote_tax_rates'     => $quoteTaxRateService->getByQuoteId($quote_id),
            'items'               => $items,
            'output_type'         => 'pdf',
            'show_item_discounts' => $show_item_discounts,
            'custom_fields'       => $custom_fields,
            'legacy_calculation'  => config_item('legacy_calculation'),
        ];

        $html = view('quote_templates/pdf/' . $quote_template, $data)->render();

        return pdf_create(
            $html,
            trans('quote') . '_' . str_replace(['\\', '/'], '_', $quote->quote_number),
            $stream,
            $quote->quote_password
        );
    }

    /**
     * Get custom field values for a given table and ID.
     * Helper method to retrieve custom field values from database.
     *
     * @param string $table Custom field table name
     * @param int    $id    Record ID
     *
     * @return array Array of custom field values
     */
    protected static function getCustomFieldValues(string $table, int $id): array
    {
        $modelClass = match($table) {
            'ip_invoice_custom' => \Modules\Core\Models\InvoiceCustom::class,
            'ip_quote_custom'   => \Modules\Core\Models\QuoteCustom::class,
            'ip_client_custom'  => \Modules\Core\Models\ClientCustom::class,
            'ip_user_custom'    => \Modules\Core\Models\UserCustom::class,
            'ip_payment_custom' => \Modules\Core\Models\PaymentCustom::class,
            default             => null,
        };

        if ( ! $modelClass) {
            return [];
        }

        // Get the ID field name from the table
        $idField = str_replace('_custom', '_id', str_replace('ip_', '', $table));

        // Get all custom field records for this ID
        $records = $modelClass::query()->where($idField, $id)->get();

        // Convert to array format
        $values = [];
        foreach ($records as $record) {
            $values[] = $record->toArray();
        }

        return $values;
    }
}

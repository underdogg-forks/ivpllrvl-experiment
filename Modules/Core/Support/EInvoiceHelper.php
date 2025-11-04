<?php

namespace Modules\Core\Support;

use stdClass;

class EInvoiceHelper
{
    /**
     * Generate XML invoice file.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param mixed $invoice Invoice data
     * @param mixed $items Invoice items
     * @param string $xml_lib XML library/template to use
     * @param string $filename Output filename
     * @param mixed $options Additional options
     * @return string Path to generated XML file
     */
    public static function generate_xml_invoice_file($invoice, $items, string $xml_lib, string $filename, $options): string
    {
        $className = '\\Modules\\Core\\Libraries\\XMLtemplates\\' . $xml_lib . 'Xml';
        
        $xmlGenerator = new $className([
            'invoice'  => $invoice,
            'items'    => $items,
            'filename' => $filename,
            'options'  => $options,
        ]);

        $xmlGenerator->xml();

        return UPLOADS_TEMP_FOLDER . $filename . '.xml';
    }

    /**
     * Include RDF metadata for PDF/A compliance.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param string $embedXml XML filename to embed
     * @param string $urn URN for the document type
     * @return string RDF metadata XML
     */
    public static function include_rdf(string $embedXml, string $urn = 'factur-x'): string
    {
        return '<rdf:Description rdf:about="" xmlns:zf="urn:' . $urn . ':pdfa:CrossIndustryDocument:invoice:1p0#">' . "\n"
            . '  <zf:DocumentType>INVOICE</zf:DocumentType>' . "\n"
            . '  <zf:DocumentFileName>' . $embedXml . '</zf:DocumentFileName>' . "\n"
            . '  <zf:Version>1.0</zf:Version>' . "\n"
            . '  <zf:ConformanceLevel>COMFORT</zf:ConformanceLevel>' . "\n"
            . '</rdf:Description>' . "\n";
    }

    /**
     * Get available XML template files for e-invoicing.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @return array Array of available XML templates
     */
    public static function get_xml_template_files(): array
    {
        $xml_template_items = [];
        $path               = APPPATH . 'helpers/XMLconfigs/';
        $xml_config_files   = is_dir($path) ? array_diff(scandir($path), ['.', '..']) : [];

        foreach ($xml_config_files as $key => $xml_config_file) {
            $xml_config_files[$key] = str_replace('.php', '', $xml_config_file);
            $configFile             = $path . $xml_config_files[$key] . '.php';

            if (file_exists($configFile)) {
                $xml_setting = [];
                include $configFile;

                $generator = $xml_config_files[$key];
                if ( ! empty($xml_setting['generator'])) {
                    $generator = $xml_setting['generator'];
                }

                if (file_exists(APPPATH . 'libraries/XMLtemplates/' . $generator . 'Xml.php')) {
                    $xml_template_items[$xml_config_files[$key]]
                        = $xml_setting['full-name']
                        . ' - '
                        . get_country_name(trans('cldr'), $xml_setting['countrycode']);
                }
            }
        }

        return $xml_template_items;
    }

    /**
     * Get full name of XML template.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param string $xml_id XML template ID
     * @return string|null Full template name with country
     */
    public static function get_xml_full_name(string $xml_id)
    {
        $configFile = APPPATH . 'helpers/XMLconfigs/' . $xml_id . '.php';

        if ( ! file_exists($configFile)) {
            return;
        }

        $xml_setting = [];
        include $configFile;

        if (isset($xml_setting['legacy_calculation'])) {
            config(['legacy_calculation' => ! empty($xml_setting['legacy_calculation'])]);
        }

        return $xml_setting['full-name'] . ' - ' . get_country_name(trans('cldr'), $xml_setting['countrycode']);
    }

    /**
     * Get list of active admin users.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param string|int $user_id Optional user ID to filter
     * @return array Array of active admin users
     */
    public static function get_admin_active_users($user_id = ''): array
    {
        $query = \Modules\Core\Models\User::query()->where('user_type', '1')
            ->where('user_active', '1');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        return $query->get()->toArray();
    }

    /**
     * Get required fields for e-invoice generation.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param object|null $client Client object
     * @param string|int $user_id User ID
     * @return object Object containing required field flags
     */
    public static function get_req_fields_einvoice($client = null, $user_id = ''): object
    {
        $cid          = empty($client->client_id) ? 0 : $client->client_id;
        $c            = new stdClass();
        $c->address_1 = $cid && $client->client_address_1 == '' ? 1 : 0;
        $c->zip       = $cid && $client->client_zip == '' ? 1 : 0;
        $c->city      = $cid && $client->client_city == '' ? 1 : 0;
        $c->country   = $cid && $client->client_country == '' ? 1 : 0;
        $c->company   = $cid && $client->client_company == '' ? 1 : 0;
        $c->tax_code  = $cid && $client->client_tax_code == '' ? 1 : 0;
        $c->vat_id    = $cid && $client->client_vat_id == '' ? 1 : 0;

        if ($c->company + $c->vat_id === 2) {
            $c->company = 0;
            $c->vat_id  = 0;
        }

        $total_empty_fields_client = 0;
        foreach ($c as $val) {
            $total_empty_fields_client += $val;
        }

        $c->einvoicing_empty_fields = $total_empty_fields_client;
        $c->show_table              = (int) ( ! $c->einvoicing_empty_fields);

        $req_fields                = new stdClass();
        $req_fields->clients[$cid] = $c;

        if (empty($user_id)) {
            $req_fields->users[$_SESSION['user_id']] = null;
        }

        $show_table = 0;

        $users = self::get_admin_active_users($user_id);
        foreach ($users as $o) {
            $u            = new stdClass();
            $u->address_1 = $o->user_address_1 != '' ? 0 : 1;
            $u->zip       = $o->user_zip != '' ? 0 : 1;
            $u->city      = $o->user_city != '' ? 0 : 1;
            $u->country   = $o->user_country != '' ? 0 : 1;
            $u->company   = $o->user_company != '' ? 0 : 1;
            $u->tax_code  = $o->user_tax_code != '' ? 0 : 1;
            $u->vat_id    = $o->user_vat_id != '' ? 0 : 1;

            if ($u->company + $u->vat_id === 2) {
                $u->company = 0;
                $u->vat_id  = 0;
            }

            $total_empty_fields_user = 0;
            foreach ($u as $val) {
                $total_empty_fields_user += $val;
            }

            $u->einvoicing_empty_fields = $total_empty_fields_user;

            $u->tr_show_address_1 = $u->address_1 + $c->address_1 > 0 ? 1 : 0;
            $u->tr_show_zip       = $u->zip + $c->zip > 0 ? 1 : 0;
            $u->tr_show_city      = $u->city + $c->city > 0 ? 1 : 0;
            $u->tr_show_country   = $u->country + $c->country > 0 ? 1 : 0;
            $u->tr_show_company   = $u->company + $c->company > 0 ? 1 : 0;
            $u->tr_show_tax_code  = $u->tax_code + $c->tax_code > 0 ? 1 : 0;
            $u->tr_show_vat_id    = $u->vat_id + $c->vat_id > 0 ? 1 : 0;

            $u->show_table = $u->tr_show_address_1
            + $u->tr_show_zip
            + $u->tr_show_city
            + $u->tr_show_country
            + $u->tr_show_company
            + $u->tr_show_tax_code
            + $u->tr_show_vat_id > 0 ? 1 : 0;

            $u->user_name = $o->user_name;

            $req_fields->users[$o->user_id] = $u;
            $show_table += $u->show_table;
        }

        $req_fields->show_table = $show_table;

        return $req_fields;
    }

    /**
     * Get e-invoice usage data and statistics.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param object $invoice Invoice object
     * @param array $items Invoice items
     * @param bool $full Whether to return full data
     * @return object E-invoice usage data
     */
    public static function get_einvoice_usage($invoice, array $items, $full = true): object
    {
        $einvoice       = new stdClass();
        $einvoice->name = false;
        $einvoice->user = false;

        if ( ! get_setting('einvoicing')) {
            return $einvoice;
        }

        $on = ($invoice->client_einvoicing_active > 0 && $invoice->client_einvoicing_version != '');
        if ($on) {
            $einvoice->name = $invoice->client_einvoicing_version;
            if ($full) {
                $einvoice->name = self::get_xml_full_name($einvoice->name);
                $on             = ! self::items_tax_usages_bad($items);
            }

            if ($on) {
                $on = (bool) $invoice->user_tax_code;
                if ($on && $items && isset($items[0]->item_tax_rate_percent) && $items[0]->item_tax_rate_percent) {
                    $on = $invoice->user_company && $invoice->user_vat_id;
                }
            }

            $einvoice->user = $on;
        }

        return $einvoice;
    }

    /**
     * Get tax usage information from invoice items.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param array $items Invoice items
     * @return array Tax usage data
     */
    public static function get_items_tax_usages($items): array
    {
        $checks = [[], []];

        foreach ($items as $item) {
            if ($item->item_tax_rate_percent) {
                $checks[1][] = $item->item_id;
            } else {
                $checks[0][] = $item->item_id;
            }
        }

        return $checks;
    }

    /**
     * Check if items have invalid tax usage for e-invoicing.
     *
     * @origin Modules/Core/Helpers/e-invoice_helper.php
     *
     * @param array $items Invoice items
     * @return mixed False if valid, error data if invalid
     */
    public static function items_tax_usages_bad($items): mixed
    {
        if (config_item('legacy_calculation')) {
            return false;
        }

        $checks = self::get_items_tax_usages($items);

        if (count($checks[0]) !== 0 && count($checks[1]) !== 0) {
            session()->flash(
                'alert_warning',
                '<h3 class="pull-right"><a class="btn btn-default" href="javascript:check_items_tax_usages(true);"><i class="fa fa-cogs"></i> ' . trans('view') . '</a></h3>'
                . trans('items_tax_usages_bad_set')
            );

            return $checks;
        }

        return false;
    }
}

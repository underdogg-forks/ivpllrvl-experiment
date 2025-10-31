<?php

namespace Modules\Core\Support;

use Modules\Core\Models\Setting;

/**
 * NumberHelper.
 *
 * Static helper class converted from procedural functions.
 */
class NumberHelper
{
    /**
     * Return a formated amount as a currency based on the system settings, e.g. 1.234,56 €.
     *
     * @param $amount
     */
    public static function format_currency($amount): string
    {
        $currency_symbol           = Setting::getValue('currency_symbol') ?? '$';
        $currency_symbol_placement = Setting::getValue('currency_symbol_placement') ?? 'before';
        $thousands_separator       = Setting::getValue('thousands_separator') ?? ',';
        $decimal_point             = Setting::getValue('decimal_point') ?? '.';
        $decimals                  = $decimal_point ? (int) (Setting::getValue('tax_rate_decimal_places') ?? 2) : 0;
        $amount                    = (float) (is_numeric($amount) ? $amount : standardize_amount($amount));

        if ($currency_symbol_placement == 'before') {
            return $currency_symbol . number_format($amount, $decimals, $decimal_point, $thousands_separator);
        }

        if ($currency_symbol_placement == 'afterspace') {
            return number_format($amount, $decimals, $decimal_point, $thousands_separator) . '&nbsp;' . $currency_symbol;
        }

        return number_format($amount, $decimals, $decimal_point, $thousands_separator) . $currency_symbol;
    }

    /**
     * Return a formated amount based on the system settings, e.g. 1.234,56.
     *
     *
     * @return null|string
     */
    public static function format_amount($amount = null)
    {
        if ($amount) {
            $thousands_separator = Setting::getValue('thousands_separator') ?? ',';
            $decimal_point       = Setting::getValue('decimal_point') ?? '.';
            $decimals            = $decimal_point ? (int) (Setting::getValue('tax_rate_decimal_places') ?? 2) : 0;
            $amount              = is_numeric($amount) ? $amount : standardize_amount($amount);

            return number_format($amount, $decimals, $decimal_point, $thousands_separator);
        }
    }

    /**
     * Return a formated amount as a quantity based on the system settings, e.g. 1.234,56.
     *
     *
     * @return null|string
     */
    public static function format_quantity($amount = null)
    {
        if ($amount) {
            $thousands_separator = Setting::getValue('thousands_separator') ?? ',';
            $decimal_point       = Setting::getValue('decimal_point') ?? '.';
            $decimals            = $decimal_point ? (int) (Setting::getValue('default_item_decimals') ?? 2) : 0;
            $amount              = is_numeric($amount) ? $amount : standardize_amount($amount);

            return number_format($amount, $decimals, $decimal_point, $thousands_separator);
        }
    }

    /**
     * Return a standardized amount for database based on the system settings, e.g. 1234.56.
     *
     * @param $amount
     */
    public static function standardize_amount($amount): float|int|string|array|false|null
    {
        if ($amount && ! is_numeric($amount)) {
            $thousands_separator = Setting::getValue('thousands_separator') ?? ',';
            $decimal_point       = Setting::getValue('decimal_point') ?? '.';

            if ($thousands_separator == '.' && ! mb_substr_count($amount, ',') && mb_substr_count($amount, '.') > 1) {
                $amount[mb_strrpos($amount, '.')] = ','; // Replace last position of dot to comma
            }

            if ($thousands_separator) {
                $amount = strtr($amount, [$thousands_separator => '', $decimal_point => '.']);
            } else {
                $amount = strtr($amount, [$decimal_point => '.']);
            }
        }

        return $amount;
    }
}

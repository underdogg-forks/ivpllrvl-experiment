<?php

namespace Modules\Core\Support;

/**
 * PaymentsHelper.
 *
 * Static helper class converted from procedural functions.
 */
class PaymentsHelper
{
    public static function get_currencies(): array
    {
        //retrieve the available currencies
        $currencies    = new ISOCurrencies();
        $ISOCurrencies = [];
        foreach ($currencies as $currency) {
            $ISOCurrencies[$currency->getCode()] = $currency->getCode();
        }

        return $ISOCurrencies;
    }
}

<?php




use Money\Currencies\ISOCurrencies;

function get_currencies(): array
{
    //retrieve the available currencies
    $currencies    = new ISOCurrencies();
    $ISOCurrencies = [];
    foreach ($currencies as $currency) {
        $ISOCurrencies[$currency->getCode()] = $currency->getCode();
    }

    return $ISOCurrencies;
}

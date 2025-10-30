<?php



namespace Modules\Core\Support;

/**
 * CountryHelper
 *
 * Static helper class converted from procedural functions.
 */
class CountryHelper
{
    /**
     * Returns an array list of cldr => country, translated in the language $cldr.
     * If there is no translated country list, return the english one.
     *
     * @param $cldr
     *
     * @return mixed
     */
    public static function get_country_list(string $cldr)
    {
        if (file_exists(APPPATH . 'helpers/country-list/' . $cldr . '/country.php')) {
            return include APPPATH . 'helpers/country-list/' . $cldr . '/country.php';
        }

        return include APPPATH . 'helpers/country-list/en/country.php';
    }

    /**
     * Returns the countryname of a given $countrycode, translated in the language $cldr.
     *
     * @param $cldr
     * @param $countrycode
     *
     * @return mixed
     */
    public static function get_country_name($cldr, $countrycode)
    {
        $countries = get_country_list($cldr);

        return $countries[$countrycode] ?? $countrycode;
    }

}

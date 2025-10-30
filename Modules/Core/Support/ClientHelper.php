<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Modules\Core\Services\LegacyBridge;

/**
 * ClientHelper
 * 
 * Static helper class converted from procedural functions.
 */
class ClientHelper
{
    /**
     * @param obj|int $client     (or id - since 1.6.3)
     * @param bool    $show_title - since 1.6.3
     */
    public static function format_client($client, $show_title = true): string
    {
        // Get an id
        if ($client && is_numeric($client)) {
            $bridge = LegacyBridge::getInstance();
            if ( ! property_exists($CI, 'mdl_clients')) {
                $bridge->getRawInstance()->load->model('clients/mdl_clients');
            }
    
            $client = $CI->mdl_clients->get_by_id($client);
        }
    
        // Not exist or find, Stop.
        if (empty($client->client_name)) {
            return '';
        }
    
        $client_title = '';
        if ($show_title && ! empty($client->client_title)) {
            $client_title = ucfirst(in_array($client->client_title, ClientTitleEnum::VALUES, true) ? trans($client->client_title) : $client->client_title) . ' ';
        }
    
        return $client_title . $client->client_name . (empty($client->client_surname) ? '' : ' ' . $client->client_surname);
    }

    /**
     * @param string $gender
     *
     * @return string
     */
    public static function format_gender($gender)
    {
        if ($gender == 0) {
            return trans('gender_male');
        }
    
        if ($gender == 1) {
            return trans('gender_female');
        }
    
        return trans('gender_other');
    }

}

<span class="client-address-street-line">
    {!! $client->client_address_1 ? e($client->client_address_1) . '<br>' : '' !!}
</span>
<span class="client-address-street-line">
    {!! $client->client_address_2 ? e($client->client_address_2) . '<br>' : '' !!}
</span>
<span class="client-address-town-line">
    {!! $client->client_city ? e($client->client_city) . ' partial_client_address.php' : '' !!}
    {!! $client->client_state ? e($client->client_state) . ' partial_client_address.php' : '' !!}
    {!! $client->client_zip ? e($client->client_zip) : '' !!}
</span>
<span class="client-address-country-line">
    {!! $client->client_country ? '<br>' . get_country_name(trans('cldr'), $client->client_country) : '' !!}
</span>

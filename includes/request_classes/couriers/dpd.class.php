<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APIDPDClass
{
    protected $api_url;

    public $supported_countries = [
        'RO' => ['name' => 'Romania','numeric_iso' => '642',],
        'BG' => ['name' => 'Bulgaria','numeric_iso' => '100',],
        'HU' => ['name' => 'Hungary','numeric_iso' => '348',],
        'PL' => ['name' => 'Poland','numeric_iso' => '616',],
        'SK' => ['name' => 'Slovakia','numeric_iso' => '703',],
        'CZ' => ['name' => 'Czech Republic','numeric_iso' => '203',],
        'AT' => ['name' => 'Austria','numeric_iso' => '040',],
        'DE' => ['name' => 'Germany','numeric_iso' => '276',],
        'IT' => ['name' => 'Italy','numeric_iso' => '380',],
        'FR' => ['name' => 'France','numeric_iso' => '250',],
        'PT' => ['name' => 'Portugal','numeric_iso' => '620',],
        'GB' => ['name' => 'United Kingdom','numeric_iso' => '826',],
        'IE' => ['name' => 'Ireland','numeric_iso' => '372',],
        'NL' => ['name' => 'Netherlands','numeric_iso' => '528',],
        'BE' => ['name' => 'Belgium','numeric_iso' => '056',],
        'LU' => ['name' => 'Luxembourg','numeric_iso' => '442',],
        'DK' => ['name' => 'Denmark','numeric_iso' => '208',],
        'SE' => ['name' => 'Sweden','numeric_iso' => '752',],
        'FI' => ['name' => 'Finland','numeric_iso' => '246',],
        'ET' => ['name' => 'Estonia','numeric_iso' => '233',],
        'LV' => ['name' => 'Latvia','numeric_iso' => '428',],
        'LT' => ['name' => 'Lithuania','numeric_iso' => '440',],
        'GR' => ['name' => 'Greece','numeric_iso' => '300',],
        'ES' => ['name' => 'Spain','numeric_iso' => '724',],

    ];

    public function __construct()
    {
        $this->api_url = 'https://api.dpd.ro/v1';
    }

    public function getLatestStatus(array $parameters): ?string
    {
        $url = $this->api_url . '/track';
        $parameters += [
            'username' => get_option('dpd_username'),
            'password' => get_option('dpd_password'),
            'lastOperationOnly' => 'y',
        ];

        $request = curiero_make_request($url, 'GET', $parameters);
        $result = json_decode(wp_remote_retrieve_body($request), true)['parcels'] ?? null;

        if (!empty($result) && !isset($result[0]['error'])) {
            $result = $result[0]['operations'][0]['description'];
        } else {
            $result = null;
        }

        return $result;
    }

    public function calculate(array $parameters): ?float
    {
        $url = $this->api_url . '/calculate';
        $parameters += [
            'username' => get_option('dpd_username'),
            'password' => get_option('dpd_password'),
        ];

        $request = curiero_make_request($url, 'GET', $parameters);
        $result = json_decode(wp_remote_retrieve_body($request), true)['calculations'] ?? null;

        if (!empty($result) && !isset($result[0]['error'])) {
            $result = $result[0]['price']['total'] ?? null;
        } else {
            throw new Exception('Can not calculate DPD shipping: ' . json_encode($result));
        }
        return $result;
    }

    public function get_services(): ?array
    {
        if (
            ($service_list = get_transient('curiero_dpd_service_list'))
            && !empty($service_list)
        ) {
            return $service_list;
        }

        try {
            $url = $this->api_url . '/services';
            $parameters = [
                'username' => get_option('dpd_username'),
                'password' => get_option('dpd_password'),
            ];

            $request = curiero_make_request($url, 'GET', $parameters);
            $service_list = json_decode(wp_remote_retrieve_body($request), true)['services'] ?? [];

            if (!empty($service_list)) {
                usort($service_list, function (array $a, array $b) {
                    return $a['id'] - $b['id'];
                });

                set_transient('curiero_dpd_service_list', $service_list, DAY_IN_SECONDS);
            }
        } catch (Exception $e) {
            $service_list = [];
        }

        return $service_list;
    }

    public function get_senders(): ?array
    {
        if (
            ($sender_list = get_transient('curiero_dpd_sender_list'))
            && !empty($sender_list)
        ) {
            return $sender_list;
        }

        try {
            $url = $this->api_url . '/client/contract';
            $parameters = [
                'username' => get_option('dpd_username'),
                'password' => get_option('dpd_password'),
            ];

            $request = curiero_make_request($url, 'GET', $parameters);
            $sender_list = json_decode(wp_remote_retrieve_body($request), true)['clients'] ?? [];

            if (!empty($sender_list)) {
                set_transient('curiero_dpd_sender_list', $sender_list, DAY_IN_SECONDS);
            }
        } catch (Exception $e) {
            $sender_list = [];
        }

        return $sender_list;
    }
}

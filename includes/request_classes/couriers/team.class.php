<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APITeamClass
{
    protected $api_url;

    public function __construct()
    {
        $this->api_url = 'https://app.curiermanager.ro/cscourier/API';
    }

    public function getLatestStatus(array $parameters): ?string
    {
        $url = $this->api_url . '/get_status?';
        $parameters += [
            'api_key' => get_option('team_key'),
        ];

        $url .= http_build_query($parameters);

        $request = curiero_make_request($url);
        $result = json_decode(wp_remote_retrieve_body($request), true);

        if (!empty($result) && !empty($result['data']['status']) && $result['status'] == 'done') {
            $result = $result['data']['status'];
        } else {
            $result = null;
        }

        return $result;
    }

    public function calculate(array $parameters): ?float
    {
        $url = $this->api_url . '/get_price?';
        $parameters += [
            'api_key' => get_option('team_key'),
        ];

        $url .= http_build_query($parameters);

        $request = curiero_make_request($url);
        $result = json_decode(wp_remote_retrieve_body($request), true);

        if (empty($result['error']) && $result['status'] == 'done') {
            $price = $result['data']['price'];
        } else {
            throw new Exception('Can not calculate Team shipping: ' . json_encode($result));
        }

        return $price;
    }

    public function get_services(bool $main = true): ?array
    {
        $url = $this->api_url . '/list_services?';
        $parameters = [
            'api_key' => get_option('team_key'),
            'type' => $main ? 'main' : 'extra',
        ];

        $url .= http_build_query($parameters);

        $request = curiero_make_request($url);
        $result = json_decode(wp_remote_retrieve_body($request), true);
        $result = isset($result['error']) ? null : $result;

        return $result;
    }
}

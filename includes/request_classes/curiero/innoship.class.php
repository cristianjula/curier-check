<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroInnoshipClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v1/shipping/innoship/');
    }

    public function callMethod(string $url, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $url;

        $parameters += [
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'api_key' => get_option('innoship_api_key', ''),
        ];

        $request = curiero_make_request($url, $verb, $parameters, [], 15);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }
}

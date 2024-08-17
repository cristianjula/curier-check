<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroGLSClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v1/shipping/gls/');
    }

    public function callMethod(string $path, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $path;

        $parameters = array_merge([
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'gls_user' => get_option('GLS_user'),
            'gls_pass' => get_option('GLS_password'),
        ], $parameters);

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }
}

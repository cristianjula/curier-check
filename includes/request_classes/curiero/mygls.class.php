<?php

defined('ABSPATH') || exit;

class CurieroMyGLSClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v2/shipping/mygls/');
    }

    public function callMethod(string $path, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $path;

        $parameters = array_merge([
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'username' => get_option('MyGLS_user'),
            'password' => get_option('MyGLS_password'),
            'clientNumber' => get_option('MyGLS_clientnumber'),
        ], $parameters);

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getLockers(): ?array
    {
        if (
            !array_key_exists('mygls', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['mygls']->get_option('mygls_box_activ') === 'no'
        ) {
            return [];
        }

        if (
            ($mygls_box_list = get_transient('curiero_mygls_locker_list'))
            && !empty($mygls_box_list)
        ) {
            return $mygls_box_list;
        }

        $mygls_box_list = $this->callMethod('getLockers');

        if ($mygls_box_list['status'] === 403) {
            WC()->shipping->get_shipping_methods()['mygls']->update_option('mygls_box_activ', 'no');
            WC()->shipping->get_shipping_methods()['mygls']->update_option('box_map_activ', 'no');

            return [];
        }

        if (empty($mygls_box_list['message'])) {
            return [];
        }

        $mygls_box_list = json_decode($mygls_box_list['message'], true);
        set_transient('curiero_mygls_locker_list', $mygls_box_list, DAY_IN_SECONDS);

        return $mygls_box_list;
    }
}

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroDPDClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v1/shipping/dpd/');
    }

    public function callMethod(string $url, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $url;

        $parameters += [
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'username' => get_option('dpd_username'),
            'password' => get_option('dpd_password'),
        ];

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getDPDboxes(): array
    {
        if (
            !array_key_exists('dpd', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['dpd']->get_option('dpd_box') === 'no'
        ) {
            return [];
        }

        if (
            ($dpd_boxes = get_transient('curiero_dpd_box_list'))
            && !empty($dpd_boxes)
        ) {
            return $dpd_boxes;
        }

        $response = $this->callMethod('getLockers');

        if ($response['status'] === 200) {
            $dpd_boxes = json_decode($response['message'] ?? [], true);
            $dpd_boxes = array_map(function ($dpd_box) {
                return [
                    'id' => $dpd_box['id'],
                    'name' => $dpd_box['name'],
                    'city' => $dpd_box['city'],
                    'address' => $dpd_box['address'],
                ];
            }, $dpd_boxes);

            if (!empty($dpd_boxes)) {
                set_transient('curiero_dpd_box_list', $dpd_boxes, DAY_IN_SECONDS);
            }
        } else {
            if ($response['status'] === 403) {
                WC()->shipping->get_shipping_methods()['dpd']->update_option('dpd_box', 'no');
            }
            $dpd_boxes = [];
        }

        return $dpd_boxes;
    }
}

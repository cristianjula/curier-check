<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroSamedayClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v1/shipping/sameday/');
    }

    public function callMethod(string $url, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $url;

        $parameters += [
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'username' => get_option('sameday_username'),
            'password' => get_option('sameday_password'),
        ];

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getLockers(): ?array
    {
        if (
            !array_key_exists('sameday', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['sameday']->get_option('lockers_activ') === 'no'
        ) {
            return [];
        }

        if (
            ($lockers = get_transient('curiero_sameday_locker_list'))
            && !empty($lockers)
        ) {
            return $lockers;
        }

        $response = $this->callMethod('getLockers');

        if ($response['status'] === 200) {
            $lockers = json_decode($response['message'] ?? [], true);
            $lockers = array_map(function ($locker): array {
                return [
                    'id' => $locker['id'],
                    'name' => $locker['name'],
                    'county' => $locker['county'],
                    'city' => $locker['city'],
                    'address' => $locker['address'],
                    'supportedPayment' => $locker['supportedPayment'],
                ];
            }, $lockers);

            if (!empty($lockers)) {
                set_transient('curiero_sameday_locker_list', $lockers, DAY_IN_SECONDS);
            }
        } else {
            if ($response['status'] === 403) {
                WC()->shipping->get_shipping_methods()['sameday']->update_option('lockers_activ', 'no');
                WC()->shipping->get_shipping_methods()['sameday']->update_option('lockers_map_activ', 'no');
            }
            $lockers = [];
        }

        return $lockers;
    }
}

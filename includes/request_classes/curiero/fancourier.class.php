<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroFanClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v2/shipping/fancourier/');
    }

    public function callMethod(string $url, array $parameters = [], string $verb = 'POST'): array
    {
        $url = $this->api_url . $url;

        $parameters = array_merge([
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'fan_user' => get_option('fan_user'),
            'fan_pass' => get_option('fan_password'),
        ], $parameters);

        if (empty($parameters['fan_id'])) {
            $parameters['fan_id'] = $parameters['fan_id'] ?? get_option('fan_clientID', null);
        }

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getToken(): string
    {
        if (
            ($token = get_transient('curiero_fan_token'))
            && !empty($token)
        ) {
            return $token;
        }

        $response = $this->callMethod('getAuthToken');

        if ($response['status'] === 200) {
            $response = json_decode($response['message'], true);
            $token = $response['token'] ?? '';

            if (!empty($token)) {
                $expires_at = new \DateTimeImmutable($response['expires_at'], new \DateTimeZone('Europe/Bucharest'));
                set_transient('curiero_fan_token', $token, $expires_at->getTimestamp() - time());
            }
        } else {
            $token = '';
        }

        return $token;
    }

    public function getCollectPointList(): array
    {
        if (
            !array_key_exists('fan', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['fan']->get_option('collectpoint_activ') === 'no'
        ) {
            return [];
        }

        if (
            ($collect_points = get_transient('curiero_fan_collectpoint_list'))
            && !empty($collect_points)
        ) {
            return $collect_points;
        }

        $response = $this->callMethod('getCollectPoints');

        if ($response['status'] === 200) {
            $collect_points = json_decode($response['message'] ?? [], true);
            $collect_points = array_map(function (array $point): array {
                return [
                    'id' => $point['id'],
                    'name' => $point['name'],
                    'county' => $point['county'],
                    'locality' => $point['locality'],
                    'address' => $point['address'],
                    'routingLocation' => $point['routing_location'],
                ];
            }, $collect_points);

            if (!empty($collect_points)) {
                set_transient('curiero_fan_collectpoint_list', $collect_points, DAY_IN_SECONDS);
            }
        } else {
            if ($response['status'] === 403) {
                WC()->shipping->get_shipping_methods()['fan']->update_option('collectpoint_activ', 'no');
            }
            $collect_points = [];
        }

        return $collect_points;
    }

    public function getFanboxList(): array
    {
        if (
            !array_key_exists('fan', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['fan']->get_option('fanbox_activ') === 'no'
        ) {
            return [];
        }

        if (
            ($fanbox_list = get_transient('curiero_fan_fanbox_list'))
            && !empty($fanbox_list)
        ) {
            return $fanbox_list;
        }

        $response = $this->callMethod('getLockers');

        if ($response['status'] === 200) {
            $fanbox_list = json_decode($response['message'] ?? [], true);
            $fanbox_list = array_map(function (array $point): array {
                return [
                    'id' => $point['id'],
                    'name' => $point['name'],
                    'county' => $point['county'],
                    'locality' => $point['locality'],
                    'address' => $point['address'],
                    'routingLocation' => $point['routing_location'],
                ];
            }, $fanbox_list);

            if (!empty($fanbox_list)) {
                set_transient('curiero_fan_fanbox_list', $fanbox_list, DAY_IN_SECONDS);
            }
        } else {
            if ($response['status'] === 403) {
                WC()->shipping->get_shipping_methods()['fan']->update_option('fanbox_activ', 'no');
                WC()->shipping->get_shipping_methods()['fan']->update_option('fanbox_map_activ', 'no');
            }
            $fanbox_list = [];
        }

        return $fanbox_list;
    }
}

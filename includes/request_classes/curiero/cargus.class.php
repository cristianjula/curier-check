<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieroUCClass
{
    private $api_url;

    public function __construct()
    {
        $this->api_url = curiero_get_api_url('/v1/shipping/urgentcargus/');
    }

    public function callMethod(string $url, array $parameters = [], $verb = 'POST'): array
    {
        $url = $this->api_url . $url;

        $parameters = array_merge([
            'api_user' => get_option('user_curiero'),
            'api_pass' => get_option('password_curiero'),
            'urgent_user' => get_option('uc_username'),
            'urgent_pass' => get_option('uc_password'),
            'urgent_apiKey' => get_option('uc_apikey'),
        ], $parameters);

        $request = curiero_make_request($url, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getToken(): string
    {
        if (
            ($token = get_transient('curiero_cargus_token'))
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
                set_transient('curiero_cargus_token', $token, $expires_at->getTimestamp() - time());
            }
        } else {
            $token = '';
        }

        return $token;
    }

    public function getPudoPoints(): array
    {
        if (
            !array_key_exists('urgentcargus_courier', WC()->shipping->get_shipping_methods())
            || WC()->shipping->get_shipping_methods()['urgentcargus_courier']->get_option('ship_and_go') === 'no'
        ) {
            return [];
        }

        if (
            ($pudo_points = get_transient('curiero_cargus_pudo_list'))
            && !empty($pudo_points)
        ) {
            return $pudo_points;
        }

        $county_list = curiero_get_counties_list();
        $resultLocations = $this->callMethod('getLockers');

        if ($resultLocations['status'] === 200) {
            $pudo_points = json_decode($resultLocations['message'], true) ?? [];
            $pudo_points = array_map(function (array $pudo_point) use ($county_list): array {
                return [
                    'Id' => $pudo_point['id'],
                    'Name' => $pudo_point['name'],
                    'County' => $pudo_point['county'],
                    'County_abv' => array_search($pudo_point['county'], $county_list),
                    'City' => $pudo_point['city'],
                    'Address' => $pudo_point['address'],
                    'ServiceCOD' => $pudo_point['serviceCOD'],
                ];
            }, $pudo_points);

            if (!empty($pudo_points)) {
                set_transient('curiero_cargus_pudo_list', $pudo_points, DAY_IN_SECONDS);
            }

            usort($pudo_points, function (array $a, array $b): int {
                return $a['City'] <=> $b['City'];
            });
        } else {
            if ($resultLocations['status'] === 403) {
                WC()->shipping->get_shipping_methods()['urgentcargus_courier']->update_option('ship_and_go', 'no');
            }
            $pudo_points = [];
        }

        return $pudo_points;
    }
}

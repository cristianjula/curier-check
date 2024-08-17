<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class UrgentCargusAPI
{
    protected $api_key;

    public function __construct()
    {
        $this->api_key = get_option('uc_apikey') ?: 'c76c9992055e4e419ff7fa953c3e4569';
    }

    public function callMethod(string $function, array $parameters = [], string $verb = 'POST'): array
    {
        $url = "https://urgentcargus.azure-api.net/api/{$function}";

        $request = curiero_make_request($url, $verb, $parameters, [
            'Authorization' => 'Bearer ' . CurieRO()->container->get(CurieroUCClass::class)->getToken(),
            'Ocp-Apim-Subscription-Key' => $this->api_key,
            'Ocp-Apim-Trace' => true,
        ], 8);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }
}

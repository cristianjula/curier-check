<?php

// Exit if accessed directly

defined('ABSPATH') || exit;

class APIFanCourierClass
{
    public const URL = 'https://api.fancourier.ro';

    protected $username;

    protected $client_id;

    protected $password;

    public function __construct()
    {
        $this->username = get_option('fan_user');
        $this->password = get_option('fan_password');
        $this->client_id = get_option('fan_clientID') ?: (@reset($this->getClientIds())['id'] ?? '');
    }

    public function callCourierMethod(string $function, string $verb, array $parameters): array
    {
        $url = trailingslashit(self::URL) . trim($function, '/');
        $request = curiero_make_request($url, $verb, $parameters, [
            'Authorization' => 'Bearer ' . CurieRO()->container->get(CurieroFanClass::class)->getToken(),
        ], 8);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getClientIds(): array
    {
        if (
            ($client_ids = get_transient('curiero_fan_client_ids'))
            && !empty($client_ids)
        ) {
            return $client_ids;
        }

        $parameters = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        $response = $this->callCourierMethod('/reports/branches', 'GET', $parameters);

        if ($response['status'] === 200) {
            $response_message = json_decode($response['message'], true);
            $client_ids = $response_message['data'] ?? [];

            $client_ids = array_map(function ($client) {
                return [
                    'id' => $client['id'],
                    'name' => $client['name'],
                ];
            }, $client_ids);

            if (!empty($client_ids)) {
                set_transient('curiero_fan_client_ids', $client_ids, DAY_IN_SECONDS);
            }
        } else {
            $client_ids = [];
        }

        return $client_ids;
    }

    public function getServices(): array
    {
        if (
            ($services = get_transient('curiero_fan_services'))
            && !empty($services)
        ) {
            return $services;
        }

        $response = $this->callCourierMethod('/reports/services', 'GET', []);

        $services = [];
        if ($response['status'] === 200) {
            $response_message = json_decode($response['message'], true);
            $service_list = $response_message['data'] ?? [];

            $services = array_map(function ($service) {
                return $service['name'];
            }, $service_list);

            if (!empty($services)) {
                set_transient('curiero_fan_services', $services, DAY_IN_SECONDS);
            }
        }

        return $services;
    }

    public function getLatestStatus(array $parameters): array
    {
        $parameters = array_merge([
            'clientId' => $this->client_id,
            'language' => 'ro',
        ], $parameters);

        $response = $this->callCourierMethod('/reports/awb/tracking', 'GET', $parameters);

        if ($response['status'] === 200) {
            $response_message = json_decode($response['message'], true);
            $response_message = $response_message['data'] ?? [];

            if (empty($response_message)) {
                return [];
            }

            $awb = $response_message[0];
            if (empty($awb['events'])) {
                return [
                    'id' => 0,
                    'status' => 'AWB-ul a fost inregistrat de catre clientul expeditor.',
                ];
            }

            $latest_status = end($awb['events']) ?? [];
            if (empty($latest_status)) {
                return [];
            }

            return [
                'id' => $latest_status['id'],
                'status' => $latest_status['name'],
            ];
        } else {
            return [];
        }
    }

    public function getBankTransfers(array $parameters = []): array
    {
        $parameters = array_merge([
            'clientId' => $this->client_id,
            'date' => date('Y-m-d'),
            'perPage' => 100,
            'page' => 1,
        ], $parameters);

        $response = $this->callCourierMethod('/reports/bank-transfers', 'GET', $parameters);

        if ($response['status'] !== 200) {
            return [];
        }

        $response = json_decode($response['message'], true);
        $transfers = $response['data'] ?? [];

        $transfers = array_map(
            function ($transfer): array {
                return [
                    'data_awb' => $transfer['info']['awbDate'] ?? '',
                    'suma_incasata' => $transfer['info']['amountCollected'] ?? 0,
                    'numar_awb' => $transfer['info']['awbNumber'] ?? '',
                    'destinatar' => $transfer['recipient']['name'] ?? '',
                    'data_virament' => $transfer['info']['transferDate'] ?? '',
                    'tip_tranzactie' => $transfer['info']['transactionType'] ?? '',
                ];
            },
            $transfers,
        );

        if ($response['currentPage'] * $response['perPage'] < $response['total']) {
            ++$parameters['page'];
            $transfers = array_merge($transfers, $this->getBankTransfers($parameters));
        }

        return $transfers;
    }

    public function getTarif(array $parameters = []): float
    {
        $parameters = array_merge([
            'clientId' => $this->client_id,
        ], $parameters);

        $response = $this->callCourierMethod('/reports/awb/internal-tariff', 'GET', $parameters);

        if ($response['status'] !== 200) {
            return 0;
        }

        $response = json_decode($response['message'], true);

        if (empty($response['data'])) {
            return 0;
        }

        return (float) $response['data']['costNoVAT'] ?? 0;
    }
}

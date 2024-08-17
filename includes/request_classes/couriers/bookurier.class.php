<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APIBookurierClass
{
    protected $URL;

    public function __construct()
    {
        $this->URL = 'https://www.bookurier.ro/colete/serv';
    }

    public function callCourierMethod(string $function, string $verb, $parameters): array
    {
        $request = curiero_make_request($this->URL . '/' . $function, $verb, $parameters, [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getLatestStatus(string $awb): array
    {
        $parameters = [
            'userid' => get_option('bookurier_user'),
            'pwd' => get_option('bookurier_password'),
            'msg' => '<msg><cmd><awb>' . $awb . '</awb></cmd></msg>',
        ];

        $response = $this->callCourierMethod('get_stat.php', 'POST', $parameters);
        if (strlen($response['message']) < 3) {
            return [];
        }

        $xml = new SimpleXMLElement($response['message']);
        $status = (string) $xml->cmd->status_desc;
        $status_id = (int) $xml->cmd->status_id;

        return [$status, $status_id];
    }
}

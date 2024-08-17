<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class APIGlsClass
{
    protected $URL;

    public function __construct()
    {
        $this->URL = 'https://online.gls-romania.ro';
    }

    public function callCourierMethod(string $function, string $verb): array
    {
        $request = curiero_make_request($this->URL . '/' . $function, $verb, [], [], 10);

        return [
            'status' => (int) wp_remote_retrieve_response_code($request),
            'message' => wp_remote_retrieve_body($request),
        ];
    }

    public function getParcelStatus(string $awb): ?string
    {
        $response = $this->callCourierMethod("tt_page.php?tt_value={$awb}", 'GET');

        if ($response['status'] !== 200) {
            return null;
        }

        $dom = new CurieRO\Symfony\Component\DomCrawler\Crawler($response['message']);
        $row = $dom->filter('table tr.colored_0, table tr.colored_1')->first();

        if (!count($row)) {
            return null;
        }

        $data = array_map('trim', [
            'date' => $row->filter('td')->eq(0)->text(),
            'status' => $row->filter('td')->eq(1)->text(),
            'depot' => $row->filter('td')->eq(2)->text(),
            'info' => $row->filter('td')->eq(3)->text(),
        ]);

        return $data['status'];
    }
}

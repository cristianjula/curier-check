<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Fan::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'page_type' => get_option('fan_page_type'),
    'awb_id' => $awb,
    'fan_id' => $order->get_meta('awb_fan_client_id', true),
];

$result = CurieRO()->container->get(CurieroFanClass::class)->callMethod('viewAwb', $parameters, 'POST');

if ($result['status'] != '200') {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
}

$pdf = $result['message'];
$filename = $awb . '-awb-fan.pdf';
if (strlen($pdf) <= 100) {
    wp_die($pdf);
}

curiero_output_pdf($pdf, $filename);

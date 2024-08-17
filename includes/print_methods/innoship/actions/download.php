<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = maybe_unserialize($order->get_meta(CurieRO_Printing_Innoship::$awb_field, true));
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'awb' => $awb['awb'],
    'courier_id' => $awb['courier_id'],
    'page_type' => get_option('innoship_page_type'),
];

$result = CurieRO()->container->get(CurieroInnoshipClass::class)->callMethod('downloadAwb', $parameters, 'POST');

if ($result['status'] === 200) {
    $pdf = $result['message'];
    $filename = $awb['awb'] . '-awb-innoship.pdf';

    curiero_output_pdf($pdf, $filename);
}

wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');

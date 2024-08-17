<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Cargus::$awb_field, true);

if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$obj_urgent = CurieRO()->container->get(UrgentCargusAPI::class);

$result = $obj_urgent->callMethod('AwbDocuments', [
    'type' => 'PDF',
    'barCodes' => $awb,
    'format' => (int) (get_option('uc_print_format') ?? 0),
    'printMainOnce' => (int) (get_option('uc_print_once') ?? 0),
    'printOneAwbPerPage' => (int) 0,
], 'GET');

if ($result['status'] !== 200) {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
}

$pdf = base64_decode($result['message']);
$filename = $awb . '-awb-cargus.pdf';

curiero_output_pdf($pdf, $filename);

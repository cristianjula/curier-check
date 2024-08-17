<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_DPD::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'parcels' => $awb,
    'paper_size' => get_option('dpd_page_type'),
    'format' => 'pdf',
];

$result = CurieRO()->container->get(CurieroDPDClass::class)->callMethod('downloadAwb', $parameters, 'POST');

if ($result['status'] !== 200) {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
}

$pdf = $result['message'];
$filename = $awb . '-awb-dpd.pdf';

curiero_output_pdf($pdf, $filename);

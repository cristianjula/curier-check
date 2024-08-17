<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awbnr = $order->get_meta(CurieRO_Printing_GLS::$awb_field, true);
if (empty($awbnr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'awb' => $awbnr,
    'printer_template' => get_option('GLS_printertemplate'),
    'senderid' => get_option('GLS_senderid'),
    'all_pcls' => $order->get_meta('awb_GLS_all_pcls', true),
];

$result = CurieRO()->container->get(CurieroGLSClass::class)->callMethod('downloadPdf', $parameters, 'POST');

if ($result['status'] !== 200) {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
}

$pdf = base64_decode($result['message']);
$filename = $awbnr . '_awb_gls.pdf';

curiero_output_pdf($pdf, $filename);

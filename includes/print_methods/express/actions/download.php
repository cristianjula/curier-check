<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Express::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'awbno' => $awb,
    'format' => get_option('express_page_type'),
    'pdf' => 'true',
];

$result = CurieRO()->container->get(CurieroExpressClass::class)->callMethod('downloadAwb', $parameters, 'POST');

if ($result['status'] !== 200) {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
}

$pdf = $result['message'];
$filename = $awb . '-awb-express.pdf';

curiero_output_pdf($pdf, $filename);

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_nr = $order->get_meta(CurieRO_Printing_GLS::$awb_field, true);
$parameters = [
    'senderid' => get_option('GLS_senderid'),
    'awb' => $awb_nr,
    'all_pcls' => $order->get_meta('awb_GLS_all_pcls', true),
];

if (empty($awb_nr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$response = CurieRO()->container->get(CurieroGLSClass::class)->callMethod('deleteAwb', $parameters, 'POST');

if ($response['status'] === 200) {
    $order->delete_meta_data(CurieRO_Printing_GLS::$awb_field);
    $order->delete_meta_data('awb_GLS_all_pcls');
    $order->delete_meta_data('awb_GLS_status');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_GLS::$public_name, $order_id, $awb_nr);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b class='bad'>Eroare la stergere</b>");

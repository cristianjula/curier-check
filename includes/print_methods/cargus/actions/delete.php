<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Cargus::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$result = CurieRO()->container->get(CurieroUCClass::class)->callMethod('deleteAwb', ['barCode' => $awb], 'POST');

if ($result['status'] === 200) {
    $order->delete_meta_data(CurieRO_Printing_Cargus::$awb_field);
    $order->delete_meta_data('awb_urgent_cargus_trace_status');
    $order->delete_meta_data('op_urgent_cargus');
    $order->delete_meta_data('op_urgent_cargus_value');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Cargus::$public_name, $order_id, $awb);

    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b class='bad'> DELETE Awb: </b> <pre>" . $result['message'] . '</pre>');

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_id = $order->get_meta(CurieRO_Printing_MyGLS::$awb_field, true);
if (empty($awb_id)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$response = CurieRO()->container->get(CurieroMyGLSClass::class)->callMethod('deleteAwb', ['awb' => $awb_id], 'POST');

if ($response['status'] === 200) {
    global $wpdb;
    $wpdb->delete("{$wpdb->prefix}curiero_mygls_awb_data", ['order_id' => $order_id, 'awb_number' => $order->get_meta('awb_mygls_parcelnumber')]);

    $order->delete_meta_data(CurieRO_Printing_MyGLS::$awb_field);
    $order->delete_meta_data('awb_mygls_parcelnumber');
    $order->delete_meta_data('awb_mygls_status');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_MyGLS::$public_name, $order_id, $awb_id);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b class='bad'>Eroare la stergere</b>");

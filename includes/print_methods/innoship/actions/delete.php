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
];

$response = CurieRO()->container->get(CurieroInnoshipClass::class)->callMethod('deleteAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200) {
    $order->delete_meta_data(CurieRO_Printing_Innoship::$awb_field);
    $order->delete_meta_data('awb_innoship_courier_id');
    $order->delete_meta_data('awb_innoship_tracking_url');
    $order->delete_meta_data('awb_innoship_status');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Innoship::$public_name, $order_id, $awb['awb']);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b> Eroare la stergere: </b> <br> {$message->error}");

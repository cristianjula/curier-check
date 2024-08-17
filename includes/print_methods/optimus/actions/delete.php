<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_id = $order->get_meta(CurieRO_Printing_Optimus::$awb_field, true);
$parameters = ['awb' => $awb_id];

if (empty($awb_id)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$response = CurieRO()->container->get(CurieroOptimusClass::class)->callMethod('deleteAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200 && empty($message->succes)) {
    $order->delete_meta_data(CurieRO_Printing_Optimus::$awb_field);
    $order->delete_meta_data('awb_optimus_status');
    $order->delete_meta_data('awb_optimus_id');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Optimus::$public_name, $order_id, $awb_id);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b> Eroare la stergere: </b> <br> {$message->succes}");

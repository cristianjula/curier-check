<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_nr = $order->get_meta(CurieRO_Printing_Sameday::$awb_field, true);
$parameters = ['awb' => $awb_nr];

if (empty($awb_nr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$response = CurieRO()->container->get(CurieroSamedayClass::class)->callMethod('deleteAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200 && empty($message->error)) {
    $order->delete_meta_data(CurieRO_Printing_Sameday::$awb_field);
    $order->delete_meta_data('awb_sameday_status');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Sameday::$public_name, $order_id, $awb_nr);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b> Eroare la stergere: </b> <br> {$message->error}");

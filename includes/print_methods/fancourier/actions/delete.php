<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Fan::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = [
    'awb_id' => $awb,
    'fan_id' => $order->get_meta('awb_fan_client_id', true),
];

$result = CurieRO()->container->get(CurieroFanClass::class)->callMethod('deleteAwb', $parameters, 'POST');

if ($result['status'] !== 200) {
    wp_die("<b class='bad'>Eroare la stergere: </b>" . $result['message']);
}

$order->delete_meta_data(CurieRO_Printing_Fan::$awb_field);
$order->delete_meta_data('awb_fan_client_id');
$order->delete_meta_data('awb_fan_status_id');
$order->delete_meta_data('awb_fan_status');
$order->save_meta_data();

do_action('curiero_awb_deleted', CurieRO_Printing_Fan::$public_name, $order_id, $awb);

wp_redirect($order->get_edit_order_url());
exit;

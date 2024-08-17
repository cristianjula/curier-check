<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_nr = $order->get_meta(CurieRO_Printing_Memex::$awb_field, true);
$parameters = [
    'cancelShipmentRequest' => [
        'PackageNo' => [
            'string' => $awb_nr,
        ],
    ],
];

if (empty($awb_nr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$response = CurieRO()->container->get(CurieroMemexClass::class)->callMethod('deleteAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200 && empty($message->succes)) {
    $order->delete_meta_data(CurieRO_Printing_Memex::$awb_field);
    $order->delete_meta_data('awb_memex_status');
    $order->delete_meta_data('memex_awb_generated_date');
    $order->delete_meta_data('memex_parcels');
    $order->delete_meta_data('memex_awb_service_id');
    $order->delete_meta_data('memex_ship_from');
    $order->delete_meta_data('memex_pickup_no');
    $order->delete_meta_data('memex_pickup_date');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Memex::$public_name, $order_id, $awb_nr);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b> Eroare la stergere: </b> <br> {$message->succes}");

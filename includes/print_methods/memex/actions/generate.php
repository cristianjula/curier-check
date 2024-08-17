<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_Memex::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = $_POST['awb'];

// daca ambele coduri postale se afla in lista localitatilor pentru serviciul loco standard (ServiceID=121), atunci acesta este selectat automat
$localities = ['077106', '077040', '077085', '077041', '077086', '077145', '077191', '077160', '077042', '077190', '077010', '077096', '077095'];

if (
    (in_array($parameters['shipmentRequest']['ShipFrom']['PostCode'], $localities) || strtolower($parameters['shipmentRequest']['ShipFrom']['City']) === 'bucuresti')
    && (in_array($parameters['shipmentRequest']['ShipTo']['PostCode'], $localities) || strtolower($parameters['shipmentRequest']['ShipTo']['City']) === 'bucuresti')
) {
    $parameters['shipmentRequest']['ServiceId'] = '121';
}

if (!empty($parameters['memex_additional_services'])) {
    foreach ($parameters['memex_additional_services'] as $service) {
        $parameters['shipmentRequest']['AdditionalServices'][]['AdditionalService']['Code'] = $service;
    }
}

$parameters = apply_filters('curiero_awb_details_overwrite', $parameters, CurieRO_Printing_Memex::$public_name, $order_id);

$courier = CurieRO()->container->get(CurieroMemexClass::class);
$response = $courier->callMethod('generateAwb', $parameters, 'POST');
$message = json_decode($response['message'], true);

if ($response['status'] === 200 && $message['success']) {
    if (!$message['success'] || empty($message['awb'])) {
        wp_die($message['error']);
    }

    $awb = $message['awb'];

    if (get_option('memex_trimite_mail') == 'da') {
        CurieRO_Printing_Memex::send_mails($order_id, $awb, $parameters);
    }

    $order->update_meta_data(CurieRO_Printing_Memex::$awb_field, $awb);
    $order->update_meta_data('awb_memex_status', 'Inregistrat');
    $order->update_meta_data('memex_parcels', json_encode($parameters['shipmentRequest']['Parcels']));
    $order->update_meta_data('memex_awb_service_id', $parameters['shipmentRequest']['ServiceId']);
    $order->update_meta_data('memex_awb_generated_date', date('Y-m-d'));
    $order->update_meta_data('memex_ship_from', json_encode($parameters['shipmentRequest']['ShipFrom']));
    $order->save_meta_data();

    do_action('curiero_awb_generated', CurieRO_Printing_Memex::$public_name, $awb, $order_id);

    $account_status_response = $courier->callMethod('newAccountStatus');
    $account_status = json_decode($account_status_response['message']);

    if ($account_status->show_message) {
        set_transient('memex_account_status', $account_status->message, MONTH_IN_SECONDS);
    } else {
        delete_transient('memex_account_status');
    }

    wp_redirect($order->get_edit_order_url());
    exit;
} else {
    wp_die($response['message']);
}

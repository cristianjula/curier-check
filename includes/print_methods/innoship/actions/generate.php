<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_Innoship::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = $_POST['awb'];

$parameters = apply_filters('curiero_awb_details_overwrite', $parameters, CurieRO_Printing_Innoship::$public_name, $order_id);
$courier = CurieRO()->container->get(CurieroInnoshipClass::class);
$response = $courier->callMethod('generateAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200) {
    if (!empty($message->error)) {
        wp_die($message->error);
    }

    $awb_response_info = [
        'awb' => $message->courierShipmentId,
        'courier_id' => $message->courier,
        'tracking_url' => $message->trackPageUrl,
    ];

    $awb_status_info = [
        'status' => 'New',
        'is_final' => false,
    ];

    $awb_info_extended = array_merge($parameters, $awb_response_info);
    if (get_option('innoship_trimite_mail') === 'da' && !empty($parameters['email'])) {
        CurieRO_Printing_Innoship::send_mails($order_id, $awb_response_info['awb'], $awb_info_extended);
    }

    $order->update_meta_data(CurieRO_Printing_Innoship::$awb_field, maybe_serialize($awb_response_info));
    $order->update_meta_data('awb_innoship_status', maybe_serialize($awb_status_info));
    $order->save_meta_data();

    do_action('curiero_awb_generated', CurieRO_Printing_Innoship::$public_name, $awb_response_info['awb'], $order_id);

    $account_status_response = $courier->callMethod('newAccountStatus');
    $account_status = json_decode($account_status_response['message']);

    if ($account_status->show_message) {
        set_transient('innoship_account_status', $account_status->message, MONTH_IN_SECONDS);
    } else {
        delete_transient('innoship_account_status');
    }

    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die($response['message']);

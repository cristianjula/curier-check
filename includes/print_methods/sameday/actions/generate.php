<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_Sameday::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = $_POST['awb'];

$parameters = apply_filters('curiero_awb_details_overwrite', $parameters, CurieRO_Printing_Sameday::$public_name, $order_id);
$courier = CurieRO()->container->get(CurieroSamedayClass::class);
$response = $courier->callMethod('generateAwb', $parameters, 'POST');
$message = json_decode($response['message'], true);

if ($response['status'] === 200) {
    if (!empty($message['error'])) {
        wp_die($message['error']);
    }

    $awb = $message['id'];

    if (get_option('sameday_trimite_mail') == 'da' && !empty($parameters['email'])) {
        CurieRO_Printing_Sameday::send_mails($order_id, $awb, $parameters);
    }

    $order->update_meta_data(CurieRO_Printing_Sameday::$awb_field, $awb);
    $order->update_meta_data('awb_sameday_status', 'Inregistrat');

    if (!empty($parameters['lockerLastMile'])) {
        $lockers = $courier->getLockers();
        $locker_name = collect($lockers)->where('id', $parameters['lockerLastMile'])->first();

        $order->update_meta_data('curiero_sameday_lockers', $parameters['lockerLastMile']);
        $order->update_meta_data('curiero_sameday_locker_name', $locker_name['name']);
    }

    $order->save_meta_data();

    do_action('curiero_awb_generated', CurieRO_Printing_Sameday::$public_name, $awb, $order_id);

    $account_status_response = $courier->callMethod('newAccountStatus');
    $account_status = json_decode($account_status_response['message']);

    if ($account_status->show_message) {
        set_transient('sameday_account_status', $account_status->message, MONTH_IN_SECONDS);
    } else {
        delete_transient('sameday_account_status');
    }

    wp_redirect($order->get_edit_order_url());
    exit;
} else {
    wp_die($response['message']);
}

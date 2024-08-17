<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_Fan::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$awb_details = $_POST['awb'];
$awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, CurieRO_Printing_Fan::$public_name, $order_id);

$courier = CurieRO()->container->get(CurieroFanClass::class);
$response = $courier->callMethod('generateAwb', $awb_details, 'POST');

if ($response['status'] === 200) {
    $id = json_decode($response['message']);

    if (is_numeric($id)) {
        if (get_option('fan_trimite_mail') == 'da') {
            CurieRO_Printing_Fan::send_mails($order_id, $id, $awb_details);
        }

        $order->update_meta_data(CurieRO_Printing_Fan::$awb_field, $id);
        $order->update_meta_data('awb_fan_client_id', $awb_details['fan_id']);
        $order->update_meta_data('awb_fan_status_id', '0');
        $order->update_meta_data('awb_fan_status', 'AWB-ul a fost inregistrat de catre clientul expeditor.');
        $order->save_meta_data();

        do_action('curiero_awb_generated', CurieRO_Printing_Fan::$public_name, $id, $order_id);

        $account_status_response = $courier->callMethod('newAccountStatus');
        $account_status = json_decode($account_status_response['message']);

        if ($account_status->show_message) {
            set_transient('fancourier_account_status', $account_status->message, MONTH_IN_SECONDS);
        } else {
            delete_transient('fancourier_account_status');
        }

        wp_redirect($order->get_edit_order_url());
        exit;
    } else {
        wp_die("<b class='bad'> API FanCourier AWB: </b> <pre> {$id} </pre>");
        exit;
    }
} else {
    wp_die($response['message']);
}

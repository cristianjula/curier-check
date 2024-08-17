<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_GLS::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$awb_details = $_POST['awb'];
$awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, CurieRO_Printing_GLS::$public_name, $order_id);

$courier = CurieRO()->container->get(CurieroGLSClass::class);
$response = $courier->callMethod('generateAwb', $awb_details, 'POST');

if ($response['status'] === 200) {
    $mesage = json_decode($response['message'], true);
    $successfull = $mesage['successfull'];

    if ($successfull) {
        $awb = $mesage['pcls'][0];

        if (get_option('GLS_trimite_mail') == 'da') {
            CurieRO_Printing_GLS::send_mails($order_id, $awb, $awb_details);
        }

        $order->update_meta_data(CurieRO_Printing_GLS::$awb_field, $awb);
        $order->update_meta_data('awb_GLS_all_pcls', $mesage['all_pcls']);
        $order->update_meta_data('awb_GLS_status', 'Inregistrat');
        $order->save_meta_data();

        do_action('curiero_awb_generated', CurieRO_Printing_GLS::$public_name, $awb, $order_id);

        $account_status_response = $courier->callMethod('newAccountStatus');
        $account_status = json_decode($account_status_response['message']);

        if ($account_status->show_message) {
            set_transient('gls_account_status', $account_status->message, MONTH_IN_SECONDS);
        } else {
            delete_transient('gls_account_status');
        }

        wp_redirect($order->get_edit_order_url());
        exit;
    } else {
        $errdesc = $mesage['errdesc'];
        wp_die("<b class='bad'> GLS API: </b> <pre>" . $errdesc . '</pre>');
        exit;
    }
} else {
    wp_die($response['message']);
}

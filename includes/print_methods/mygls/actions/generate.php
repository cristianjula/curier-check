<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_MyGLS::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$awb_details = $_POST['awb'];
$awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, CurieRO_Printing_MyGLS::$public_name, $order_id);

$courier = CurieRO()->container->get(CurieroMyGLSClass::class);
$response = $courier->callMethod('generateAwb', $awb_details, 'POST');

if ($response['status'] === 200) {
    $mesage = json_decode($response['message'], true);
    $successful = $mesage['success'];

    if ($successful) {
        $awb_id = $mesage['parcelId'];
        $awb_nr = $mesage['parcelNumber'];

        if (get_option('MyGLS_trimite_mail') === 'da') {
            CurieRO_Printing_MyGLS::send_mails($order_id, $awb_nr, $awb_details);
        }

        $pdf_string = '';
        foreach ($mesage['labels'] as $label) {
            $pdf_string .= chr($label);
        }

        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}curiero_mygls_awb_data",
            [
                'order_id' => $order_id,
                'awb_number' => $awb_nr,
                'awb_data' => base64_encode(gzencode($pdf_string, 9)),
                'created_at' => current_time('mysql'),
            ]
        );

        $order->update_meta_data(CurieRO_Printing_MyGLS::$awb_field, $awb_id);
        $order->update_meta_data('awb_mygls_parcelnumber', $awb_nr);
        $order->update_meta_data('awb_mygls_status', 'Inregistrat');
        $order->save_meta_data();

        do_action('curiero_awb_generated', CurieRO_Printing_MyGLS::$public_name, $awb_id, $order_id);

        $account_status_response = $courier->callMethod('newAccountStatus');
        $account_status = json_decode($account_status_response['message']);

        if ($account_status->show_message) {
            set_transient('mygls_account_status', $account_status->message, MONTH_IN_SECONDS);
        } else {
            delete_transient('mygls_account_status');
        }

        wp_redirect($order->get_edit_order_url());
        exit;
    } else {
        $errdesc = $mesage['error'];
        wp_die("<b class='bad'> MyGLS API: </b> <pre>" . $errdesc . '</pre>');
        exit;
    }
} else {
    wp_die($response['message']);
}

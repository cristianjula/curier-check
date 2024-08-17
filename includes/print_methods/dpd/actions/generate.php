<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_DPD::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$parameters = $_POST['awb'];

if (!$parameters['recipient_pickup_office_id']) {
    unset($parameters['recipient_pickup_office_id']);
}

$parameters['contents'] = substr($parameters['contents'], 0, 100);
$parameters['obpd_return_service_id'] = $parameters['obpd_option'] ? $parameters['obpd_return_service_id'] : '';
$parameters['obpd_return_payer'] = $parameters['obpd_option'] ? $parameters['obpd_return_payer'] : '';

if ($parameters['recipient_address_country_name'] !== "RO") {
    $parameters['service_id'] = $parameters['international_service_id'];
}
unset($parameters['international_service_id']);

$parameters = apply_filters('curiero_awb_details_overwrite', $parameters, CurieRO_Printing_DPD::$public_name, $order_id);

$courier = CurieRO()->container->get(CurieroDPDClass::class);
$response = $courier->callMethod('generateAwb', $parameters, 'POST');
$message = json_decode($response['message']);

if ($response['status'] === 200) {
    if (!empty($message->error)) {
        wp_die($message->error->message);
    }

    $awb = $message->id;
    if (empty($awb)) {
        wp_die('Eroare la generare AWB: A apărut o eroare la generarea AWB-ului. Vă rugăm să încercați din nou.');
    }

    if (get_option('dpd_trimite_mail') === 'da') {
        CurieRO_Printing_DPD::send_mails($order_id, $awb, $parameters);
    }

    $order->update_meta_data(CurieRO_Printing_DPD::$awb_field, $awb);
    $order->update_meta_data('awb_dpd_status', 'Inregistrat');
    $order->save_meta_data();

    do_action('curiero_awb_generated', CurieRO_Printing_DPD::$public_name, $awb, $order_id);

    $account_status_response = $courier->callMethod('newAccountStatus');
    $account_status = json_decode($account_status_response['message']);

    if ($account_status->show_message) {
        set_transient('dpd_account_status', $account_status->message, MONTH_IN_SECONDS);
    } else {
        delete_transient('dpd_account_status');
    }

    wp_redirect($order->get_edit_order_url());
    exit;
} else {
    wp_die($response['message']);
}

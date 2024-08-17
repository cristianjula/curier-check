<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_nr = $order->get_meta(CurieRO_Printing_Bookurier::$awb_field, true);

if (empty($awb_nr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}
$awb_list = explode(',', $awb_nr);

$success = true;
foreach ($awb_list as $awb) {
    $parameters = ['awb' => $awb];
    $response = CurieRO()->container->get(CurieroBookurierClass::class)->callMethod('deleteAwb', $parameters, 'POST');
    $decoded_response = json_decode($response['message'], true);

    if ($response['status'] === 200 && !$decoded_response['success']) {
        $success = false;
    } else {
        $awb_nr = trim(str_replace(',,', ',', str_replace($awb, '', $awb_nr)), ',');
    }
}

if ($response['status'] === 200 && $success) {
    $order->delete_meta_data(CurieRO_Printing_Bookurier::$awb_field);
    $order->delete_meta_data('awb_bookurier_status');
    $order->delete_meta_data('awb_bookurier_status_id');
    $order->save_meta_data();

    do_action('curiero_awb_deleted', CurieRO_Printing_Bookurier::$public_name, $order_id, $awb_nr);
    wp_redirect($order->get_edit_order_url());
    exit;
}

wp_die("<b class='bad'>Bookurier API: Eroare la stergere AWB.</b>");

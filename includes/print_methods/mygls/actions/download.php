<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb_id = $order->get_meta(CurieRO_Printing_MyGLS::$awb_field, true);
if (empty($awb_id)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

global $wpdb;
$mygls_print_label = $wpdb->get_var(
    $wpdb->prepare("SELECT awb_data FROM {$wpdb->prefix}curiero_mygls_awb_data WHERE order_id = %d", $order_id)
);

if (empty($mygls_print_label)) {
    wp_die(__('AWB-ul nu mai este disponibil pentru descarcare. Va rugam sa il cautati in platforma MyGLS.', 'curiero-plugin'));
}

$filename = $awb_id . '_awb_gls.pdf';
curiero_output_pdf(gzdecode(base64_decode($mygls_print_label)), $filename);

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
if ($order->meta_exists(CurieRO_Printing_Cargus::$awb_field)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$awb_details = $_POST['awb'];
$awb_details['SenderClientId'] = empty($awb_details['SenderClientId']) ? '' : $awb_details['SenderClientId'];
$awb_details['OpenPackage'] = $awb_details['OpenPackage'] ? true : false;
$awb_details['SaturdayDelivery'] = $awb_details['SaturdayDelivery'] ? true : false;
$awb_details['MorningDelivery'] = $awb_details['MorningDelivery'] ? true : false;
$awb_details['PriceTableId'] = ($awb_details['PriceTableId'] == 1) ? 0 : $awb_details['PriceTableId'];
$awb_details['PackageContent'] = substr($awb_details['PackageContent'], 0, 512);

$totalWeight = 0;
for ($i = 0; $i < $awb_details['Envelopes']; ++$i) {
    $awb_details['ParcelCodes'][] = [
        'Code' => (string) ($i + count($awb_details['ParcelCodes'] ?? [])),
        'Type' => '0',
    ];
}

foreach (($awb_details['ParcelCodes'] ?? []) as $parcel_code) {
    $totalWeight += ($parcel_code['Weight'] ?? 0);
}

if (!$totalWeight) {
    $totalWeight = 1;
}
$awb_details['TotalWeight'] = $totalWeight;

$awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, CurieRO_Printing_Cargus::$public_name, $order_id);
$courier = CurieRO()->container->get(CurieroUCClass::class);
$result = $courier->callMethod('generateAwb', $awb_details, 'POST');

if ($result['status'] == '200') {
    $awb = json_decode($result['message']);
    if (is_numeric($awb)) {
        if (get_option('uc_trimite_mail') == '1') {
            CurieRO_Printing_Cargus::send_mails($order_id, $awb, $awb_details);
        }

        $order->update_meta_data(CurieRO_Printing_Cargus::$awb_field, $awb);
        $order->save_meta_data();

        do_action('curiero_awb_generated', CurieRO_Printing_Cargus::$public_name, $awb, $order_id);

        $account_status_response = $courier->callMethod('newAccountStatus');
        $account_status = json_decode($account_status_response['message']);

        if ($account_status->show_message) {
            set_transient('cargus_account_status', $account_status->message, MONTH_IN_SECONDS);
        } else {
            delete_transient('cargus_account_status');
        }

        wp_redirect($order->get_edit_order_url());
        exit;
    } else {
        wp_die("<b class='bad'> API Cargus AWB: </b> <pre>" . $result['message'] . '</pre>');
    }
} else {
    wp_die($result['message'] ?: 'Eroare Cargus: AWB-ul nu poate fi generat.');
}

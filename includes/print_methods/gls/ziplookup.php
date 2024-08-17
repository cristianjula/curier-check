<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'GLSawb_sectionid2',
        'GLS - Cod postal',
        function ($order): void {
            $order = curiero_get_order($order);

            wc_get_template(
                '/templates/ziplookup_metabox_template.php',
                [
                    'order' => $order,
                ],
                '',
                plugin_dir_path(__FILE__),
            );
        },
        curiero_get_shop_order_screen_id(),
        'side',
        'core'
    );
});

add_action('woocommerce_update_order', function (int $order_id): void {
    if (isset($_POST['UpdateZipOrder'])) {
        $order = curiero_get_order($order_id);
        $postcode = $_POST['zip_code_val'];

        if ($order->get_formatted_shipping_address()) {
            if ($order->get_shipping_postcode() === $postcode) {
                return;
            }

            $order->set_shipping_postcode($postcode);
            $order->set_shipping_country('RO');
        } else {
            if ($order->get_billing_postcode() === $postcode) {
                return;
            }

            $order->set_billing_postcode($postcode);
            $order->set_billing_country('RO');
        }

        $order->save();
    }
});

add_action('wp_ajax_curiero_fetch_zipcode', function (): void {
    global $wpdb;

    $query = "SELECT ZipCode,City,Street,County FROM {$wpdb->prefix}curiero_zipcodes";
    $keyword = $_POST['keyword'];

    if (is_numeric($keyword)) {
        $query = "{$query} WHERE ZipCode LIKE '{$keyword}%'";
    } else {
        $convertKeyword = explode(',', curiero_remove_accents(strtolower($keyword)));
        $convertedCity = curiero_remove_accents(trim($convertKeyword[0]));
        $convertedStreet = curiero_remove_accents(trim($convertKeyword[1] ?? ''));

        $query = "{$query} WHERE (City LIKE '%{$convertedCity}%' OR Street LIKE '%{$convertedCity}%')";

        if (!empty($convertedStreet)) {
            $query = "{$query} AND (City LIKE '%{$convertedStreet}%' OR Street LIKE '%{$convertedStreet}%')";
        }
    }

    $search = $wpdb->get_results("{$query} LIMIT 12");

    wp_send_json_success(
        array_map(function (object $item): array {
            return [
                'zip_code' => $item->ZipCode,
                'city' => $item->City,
                'street' => $item->Street,
                'county' => $item->County,
            ];
        }, $search)
    );
});

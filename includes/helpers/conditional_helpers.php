<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!defined('DELIVERED_AWB_STATUSES')) {
    define('DELIVERED_AWB_STATUSES', ['4', 'Livrat', 'Confirmat', 'Rambursat', 'Expedierea ta a fost livrată cu success.', '05-Livrat', 'Colete livrate', 'Colet livrat', 'Coletul a fost livrat cu succes.', 'Delivered']);
}

if (!function_exists('curiero_get_counties_list')) {
    /**
     * Get counties list.
     *
     * @param string|null $county_code
     * @return string|string[]|null
     */
    function curiero_get_counties_list(?string $county_code = null)
    {
        $counties = ['AB' => 'Alba', 'AR' => 'Arad', 'AG' => 'Arges', 'BC' => 'Bacau', 'BH' => 'Bihor', 'BN' => 'Bistrita-Nasaud', 'BT' => 'Botosani', 'BR' => 'Braila', 'BV' => 'Brasov', 'B' => 'Bucuresti', 'BZ' => 'Buzau', 'CL' => 'Calarasi', 'CS' => 'Caras-Severin', 'CJ' => 'Cluj', 'CT' => 'Constanta', 'CV' => 'Covasna', 'DB' => 'Dambovita', 'DJ' => 'Dolj', 'GL' => 'Galati', 'GJ' => 'Gorj', 'GR' => 'Giurgiu', 'HR' => 'Harghita', 'HD' => 'Hunedoara', 'IL' => 'Ialomita', 'IS' => 'Iasi', 'IF' => 'Ilfov', 'MM' => 'Maramures', 'MH' => 'Mehedinti', 'MS' => 'Mures', 'NT' => 'Neamt', 'OT' => 'Olt', 'PH' => 'Prahova', 'SJ' => 'Salaj', 'SM' => 'Satu Mare', 'SB' => 'Sibiu', 'SV' => 'Suceava', 'TR' => 'Teleorman', 'TM' => 'Timis', 'TL' => 'Tulcea', 'VS' => 'Vaslui', 'VL' => 'Valcea', 'VN' => 'Vrancea'];

        return is_null($county_code)
            ? $counties
            : ($counties[strtoupper($county_code)] ?? null);
    }
}

if (!function_exists('curiero_get_country_iso_numeric_code')) {
    /**
     * Get country ISO 3166-1 numeric code by name.
     *
     * @param string $country_name
     * @return string|null
     */
    function curiero_get_country_iso_numeric_code(string $country_name): ?string
    {
        $counties = ['Romania' => '642', 'Hungary' => '348', 'Bulgaria'  => '100', 'Greece' => '300', 'Poland' => '616', 'Czech Republic' => '203', 'Germany' => '276', 'France' => '250', 'Netherlands' => '528', 'Austria' => '40', 'Belgium' => '56', 'Denmark' => '208', 'Estonia' => '233', 'Finland' => '246', 'Italy' => '380', 'Latvia' => '428', 'Lithuania' => '440', 'Luxembourg' => '442', 'Portugal' => '620', 'Spain' => '724', 'Sweden' => '752'];

        return $counties[$country_name] ?? null;
    }
}

if (!function_exists('curiero_get_post_code')) {
    /**
     * Sanitize and get the post code.
     * If the post code is not found, it will return an empty string.
     *
     * @param string|null $county
     * @param string|null $city
     * @param string|null $postcode
     * @param string|null $country_code
     * @return string
     */
    function curiero_get_post_code(?string $county = '', ?string $city = '', ?string $postcode = '', ?string $country_code = 'RO'): string
    {
        global $wpdb;

        $postcode = preg_replace('/[^a-zA-Z0-9]/', '', $postcode);

        if (empty($county) || empty($city) || $country_code !== 'RO') {
            return $postcode ?? '';
        }

        if (strlen($county) <= 2) {
            $county = curiero_get_counties_list($county);
        }

        // Check if postcode exists
        if (!empty($postcode)) {
            $postcode = $wpdb->get_var(
                $wpdb->prepare("SELECT ZipCode FROM {$wpdb->prefix}curiero_zipcodes WHERE ZipCode=%s LIMIT 1", $postcode)
            );

            if ($postcode) {
                return $postcode;
            }
        }

        $city_versions = array_unique([
            trim($city),
            preg_replace('/\s+/', '-', trim($city)),
            preg_replace('/[\s-]+/', ' ', trim($city)),
        ]);

        $base_query = "SELECT ZipCode FROM {$wpdb->prefix}curiero_zipcodes WHERE County LIKE %s AND ";
        $county_like = "%{$wpdb->esc_like($county)}%";

        // Try exact match first
        $exact_conditions = implode(' OR ', array_fill(0, count($city_versions), 'City = %s'));
        $exact_query = $wpdb->prepare($base_query . "({$exact_conditions}) LIMIT 1", array_merge([$county_like], $city_versions));
        $result = $wpdb->get_var($exact_query);

        // If no exact match, try LIKE query
        if ($result === null) {
            $like_conditions = implode(' OR ', array_fill(0, count($city_versions), 'City LIKE %s'));
            $like_params = array_map(function (string $city) use ($wpdb): string { return "%{$wpdb->esc_like($city)}%"; }, $city_versions);
            $like_query = $wpdb->prepare($base_query . "({$like_conditions}) LIMIT 1", array_merge([$county_like], $like_params));

            $result = $wpdb->get_var($like_query);
        }

        return $result ?? '';
    }
}

if (!function_exists('curiero_get_post_id_by_meta')) {
    /**
     * Get post ID by meta.
     *
     * @param string $key
     * @param string $value
     * @return string|null
     */
    function curiero_get_post_id_by_meta(string $key, string $value): ?string
    {
        global $wpdb;
        $column = CurieRO()->woocommerce_hpos_enabled ? 'order_id' : 'post_id';
        $table = CurieRO()->woocommerce_hpos_enabled ? "{$wpdb->prefix}wc_orders_meta" : $wpdb->postmeta;

        return $wpdb->get_var(
            $wpdb->prepare("SELECT {$column} FROM {$table} WHERE meta_key=%s AND meta_value=%s LIMIT 1", $key, $value)
        );
    }
}

if (!function_exists('curiero_handle_email_template')) {
    /**
     * Handle email template.
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    function curiero_handle_email_template(string $template, array $data): string
    {
        $tabel_produse = '<table style="width: 100%; table-layout: fixed;"><tr><th align="left">Produs</th><th align="center">Cantitate</th><th align="right">Pret</th></tr>';
        foreach ($data['produse'] as $item) {
            $tabel_produse .= '<tr>';
            $tabel_produse .= '<td align="left">' . $item->get_name() . '</td>';
            $tabel_produse .= '<td align="center">' . $item->get_quantity() . '</td>';
            $tabel_produse .= '<td align="right">' . wc_price($item->get_total() + $item->get_total_tax()) . '</td>';
            $tabel_produse .= '</tr>';
        }
        $tabel_produse .= '</table>';

        return str_replace(
            [
                '[nr_comanda]',
                '[data_comanda]',
                '[nr_awb]',
                '[tabel_produse]',
                '[total_comanda]',
                '[sameday_easybox]',
                '[innoship_link_urmarire]',
                '[innoship_denumire_curier]',
            ],
            [
                $data['nr_comanda'],
                $data['data_comanda'],
                $data['awb'],
                $tabel_produse,
                $data['total_comanda'],
                $data['sameday_easybox'] ?? '',
                $data['innoship_link_urmarire'] ?? '',
                $data['innoship_denumire_curier'] ?? '',
            ],
            $template
        );
    }
}

if (!function_exists('curiero_get_available_shipping_methods')) {
    /**
     * Get available shipping methods.
     *
     * @return array
     */
    function curiero_get_available_shipping_methods(): array
    {
        return function_exists('WC')
            ? WC()->shipping->get_shipping_methods()
            : [];
    }
}

if (!function_exists('curiero_autogenerate_invoice')) {
    /**
     * Autogenerate invoice.
     *
     * @param int $order_id
     * @param string $awb_status
     * @return void
     *
     * @throws Exception
     */
    function curiero_autogenerate_invoice(int $order_id, string $awb_status): void
    {
        if (!in_array($awb_status, DELIVERED_AWB_STATUSES)) {
            return;
        }

        $order = curiero_get_order($order_id);

        $invoice_systems = [
            'smartbill' => [
                'option' => 'enable_automatic_smartbill',
                'function' => 'smartbill_create_document',
                'meta_key' => 'smartbill_private_link',
            ],
            'oblio' => [
                'option' => 'enable_automatic_oblio',
                'function' => '_wp_oblio_generate_invoice',
                'meta_key' => 'oblio_invoice_link',
            ],
            'fgo' => [
                'option' => 'enable_automatic_fgo',
                'function' => [WC_FGO_Premium_Order::class, 'buildFactura'],
                'meta_key' => '_fgo_invoice_link',
            ],
        ];

        foreach ($invoice_systems as $system => $config) {
            if (get_option($config['option']) === '1') {
                if (
                    (is_callable($config['function']))
                    && empty($order->get_meta($config['meta_key'], true))
                ) {
                    call_user_func($config['function'], $order_id);
                }

                return;  // Exit after processing the active invoice system
            }
        }
    }
}

if (!function_exists('curiero_mark_order_complete')) {
    /**
     * Mark order complete.
     *
     * @param string $order_id
     * @param string $awb_status
     * @param string $is_active
     * @return void
     *
     * @throws Exception
     */
    function curiero_mark_order_complete(string $order_id, string $awb_status, string $is_active): void
    {
        if (
            $is_active !== 'da'
            || empty($order_id)
        ) {
            return;
        }

        if (in_array($awb_status, DELIVERED_AWB_STATUSES)) {
            $order = curiero_get_order($order_id);
            $status = str_replace('wc-', '', apply_filters('curiero_order_complete_status', 'completed'));

            $order->update_status($status);
        }
    }
}

if (!function_exists('curiero_string_to_float')) {
    /**
     * Convert string to float with WC decimals and
     * trimming trailing zeros.
     *
     * @param string $value
     * @return float
     */
    function curiero_string_to_float(?string $value): float
    {
        return (float) wc_format_decimal($value, wc_get_price_decimals(), true);
    }
}

if (!function_exists('curiero_extract_order_details')) {
    /**
     * Extract order details.
     *
     * @param WC_Abstract_Order $order
     * @return array
     */
    function curiero_extract_order_details(WC_Abstract_Order $order): array
    {
        $type = $order->has_shipping_address() ? 'shipping' : 'billing';
        $order_info = $order->get_address($type);

        $country_short = $order_info['country'] ?: 'RO';
        $state_short = $order_info['state'];
        $state_long = WC()->countries->get_states($country_short)[$state_short] ?? curiero_get_counties_list($state_short);

        return array_map('trim', [
            'number' => method_exists($order, 'get_order_number') ? $order->get_order_number() : $order->get_id(),
            'name' => $order->{"get_formatted_{$type}_full_name"}(),
            'company' => $order_info['company'] ?? $order->get_billing_company(),
            'phone' => $order_info['phone'] ?: $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
            'country_short' => $country_short,
            'country_long' => curiero_remove_accents(WC()->countries->countries[$country_short]),
            'state_short' => $state_short,
            'state_long' => $state_long,
            'city' => $order_info['city'],
            'postcode' => curiero_get_post_code($state_long, $order_info['city'], $order_info['postcode'], $country_short),
            'address_full' => "{$order_info['address_1']} {$order_info['address_2']}",
            'address_1' => $order_info['address_1'],
            'address_2' => $order_info['address_2'],
        ]);
    }
}

if (!function_exists('curiero_remove_accents')) {
    /**
     * Remove accents.
     *
     * @param mixed $value
     * @return mixed
     */
    function curiero_remove_accents($value)
    {
        return is_string($value)
            ? remove_accents($value)
            : $value;
    }
}

if (!function_exists('curiero_strip_special_chars')) {
    /**
     * Remove special chars.
     *
     * @param string $string
     * @return string
     */
    function curiero_strip_special_chars(string $string): string
    {
        return str_replace('&', '', $string);
    }
}

if (!function_exists('curiero_shipping_option_value_is')) {
    /**
     * Check if the given option is the expected value for the given shipping method.
     *
     * @param string $shipping_method_name
     * @param string $option_name
     * @param mixed $value
     * @return bool
     */
    function curiero_shipping_option_value_is(string $shipping_method_name, string $option_name, $value): bool
    {
        $shipping_method = WC()->shipping->get_shipping_methods()[$shipping_method_name] ?? false;
        if (!$shipping_method) {
            return false;
        }

        return $shipping_method->get_option($option_name, null) === $value;
    }
}

if (!function_exists('curiero_ob_end_clean_all')) {
    /**
     * Clean all output buffers.
     *
     * @return void
     */
    function curiero_ob_end_clean_all(): void
    {
        $levels = ob_get_level();
        for ($i = 0; $i < $levels; ++$i) {
            ob_end_clean();
        }
    }
}

if (!function_exists('curiero_output_pdf')) {
    /**
     * Output PDF from bytes.
     *
     * @param string $content
     * @param string $filename
     * @param string $disposition
     * @return void
     */
    function curiero_output_pdf(string $content, string $filename, string $disposition = 'inline'): void
    {
        if (!in_array($disposition, ['inline', 'attachment'])) {
            throw new \BadMethodCallException('Invalid disposition type');
        }

        curiero_ob_end_clean_all();
        ob_start();

        header('Content-Type: application/pdf');
        header("Content-Disposition: {$disposition}; filename={$filename}");
        header('Accept-Ranges: bytes');
        echo $content;

        ob_end_flush();
        exit;
    }
}

if (!function_exists('curiero_order_item_shipping_tag')) {
    /**
     * Get shipping tag for item.
     *
     * @param WC_Order_Item_Shipping $shipping_item
     * @return false|int|string
     */
    function curiero_order_item_shipping_tag(WC_Order_Item_Shipping $shipping_item)
    {
        $shipping_method_id = $shipping_item->get_method_id();

        if (array_key_exists($shipping_method_id, WC()->shipping->get_shipping_methods())) {
            return array_search(
                get_class(WC()->shipping->get_shipping_methods()[$shipping_method_id]),
                CurieRO()->shipping_methods->get_active(),
                true
            );
        }

        return false;
    }
}

if (!function_exists('curiero_force_locker_shipping_address')) {
    /**
     * Force locker shipping address on Order.
     *
     * @param WC_Order $order
     * @param string $locker_name
     * @param string $locker_address
     * @return void
     */
    function curiero_force_locker_shipping_address(WC_Order &$order, string $locker_name = '', string $locker_address = ''): void
    {
        $billing_address = trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        $shipping_address = trim("{$order->get_shipping_address_1()} {$order->get_shipping_address_2()}");

        if ($billing_address !== $shipping_address) {
            $order->update_meta_data('original_shipping_address', $shipping_address);
        }

        $order->set_shipping_address_1($locker_name);
        $order->set_shipping_address_2($locker_address);
    }
}

if (!function_exists('curiero_calculate_package_details')) {
    /**
     * Calculate package details.
     * Used in CurieRO_Shipping_Method::calculate_shipping method.
     *
     * @param WC_Shipping_Method $method
     * @param array $package
     * @return array
     */
    function curiero_calculate_package_details(WC_Shipping_Method $method, array $package): array
    {
        if (empty($package['contents'])) {
            return [];
        }

        $includeCouponsInShipping = $method->get_option('prag_gratis_cupoane');

        $prices = ['declaredValue' => 0, 'codValue' => 0, 'cartValue' => 0];
        $cartDimensions = curiero_package_dimensions_calculator($package['contents']);

        foreach ($package['contents'] as $product) {
            if ($product['data']->is_virtual()) {
                continue;
            }

            $prices['declaredValue'] += round($product['line_total'] + $product['line_tax'], 2);
            $prices['codValue'] += round($product['line_total'] + $product['line_tax'], 2);
            if ($includeCouponsInShipping === 'yes') {
                $prices['cartValue'] += round($product['line_total'] + $product['line_tax'], 2);
            } else {
                $prices['cartValue'] += round($product['line_subtotal'] + $product['line_subtotal_tax'], 2);
            }
        }

        return apply_filters('curiero_calculate_package_details_overwrite', array_merge($cartDimensions, [
            'declaredValue' => (float) $prices['declaredValue'],
            'codValue' => (float) $prices['codValue'],
            'cartValue' => (float) $prices['cartValue'],
        ]), $package, $method);
    }
}

if (!function_exists('curiero_extract_order_items_details')) {
    /**
     * Calculate order details.
     * Used in CurieRO_Printing_Method::getAwbDetails method.
     *
     * @param WC_Abstract_Order $order
     * @param string|null $content_description
     * @return array
     */
    function curiero_extract_order_items_details(WC_Abstract_Order $order, ?string $content_description = null): array
    {
        $contents = '';
        $packing = '';

        $order_items = $order->get_items();
        $cartDimensions = curiero_package_dimensions_calculator($order_items);

        foreach ($order_items as $order_item) {
            /** @var WC_Product */
            $product = $order_item->get_product();

            if ($product->is_virtual()) {
                continue;
            }

            $cod_pro = $product->get_sku() ?: $order_item->get_product_id();

            $name_pro = $product->get_name();
            $name_pro = str_replace([',', '|', '\\', '/'], '-', $name_pro);

            $val_dec_pro = $order_item->get_total();
            $val_dec_pro = number_format($val_dec_pro, wc_get_price_decimals(), '.', '');

            $packing .= strip_tags($name_pro . '/' . $cod_pro . '/' . $order_item->get_quantity() . '/' . $val_dec_pro . '|');
            switch ($content_description) {
                case '1':
                case 'name':
                    $contents .= ', ' . $order_item->get_quantity() . ' x ' . $order_item->get_name();

                    break;

                case 'sku':
                    $contents .= ', ' . $order_item->get_quantity() . ' x ' . $cod_pro;

                    break;

                case 'both':
                    $contents .= ', ' . $order_item->get_quantity() . ' x ' . $order_item->get_name() . '/' . $cod_pro;

                    break;

                default:
                    break;
            }
        }

        $contents = ltrim($contents, ', ');
        $packing = rtrim($packing, '|');

        $price_total = number_format((float) $order->get_total(), wc_get_price_decimals(), '.', '');
        $price_excl_shipping = number_format((float) $order->get_total() - $order->get_shipping_total() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '');

        return apply_filters('curiero_extract_order_items_details_overwrite', array_merge($cartDimensions, [
            'contents' => $contents,
            'packing' => $packing,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping,
        ]), $order);
    }
}

if (!function_exists('curiero_calculate_self_shipping_costs')) {
    /**
     * Calculate self shipping costs.
     *
     * @param WC_Shipping_Method $method
     * @param array $shipping_details
     * @param int $extra_km
     * @return float
     */
    function curiero_calculate_self_shipping_costs(WC_Shipping_Method $method, array $shipping_details, int $extra_km = 0): float
    {
        $shipping_state = WC()->session->get('customer')['shipping_state'] ?? '';
        if (empty($shipping_state)) {
            return curiero_string_to_float($method->get_option('tarif_implicit'));
        }

        $extra_km_rate = curiero_string_to_float($method->get_option('pret_km_suplimentar', 0));
        $extra_kg_rate = curiero_string_to_float($method->get_option('pret_kg_suplimentar', 0));
        $extra_kg_threshold = curiero_string_to_float($method->get_option('prag_gratis_kg', 0));
        $extra_kg = max($shipping_details['weight'] - $extra_kg_threshold, 0);

        if ($shipping_state === 'B') {
            $free_shipping_threshold = curiero_string_to_float($method->get_option('prag_gratis_Bucuresti'));
            $flat_rate = curiero_string_to_float($method->get_option('suma_fixa_Bucuresti'));
        } else {
            $free_shipping_threshold = curiero_string_to_float($method->get_option('prag_gratis_provincie'));
            $flat_rate = curiero_string_to_float($method->get_option('suma_fixa_provincie'));
        }

        if ($shipping_details['cartValue'] >= $free_shipping_threshold) {
            $flat_rate = 0;
        }

        return $flat_rate + ($extra_km_rate * $extra_km) + ($extra_kg_rate * $extra_kg);
    }
}

if (!function_exists('curiero_package_dimensions_calculator')) {
    /**
     * Calculate package dimensions.
     *
     * Logic: Stack items on the smallest side.
     * Put multiple items side by side on the smallest side.
     *
     * @param array $package
     * @return array<string, int>
     */
    function curiero_package_dimensions_calculator(array $package): array
    {
        $cartWeight = 0;
        $cartProductDimensions = [];
        $cartDimensionsSum = ['height' => 0, 'length' => 0, 'width' => 0];

        if (empty($package)) {
            return array_merge($cartDimensionsSum, [
                'weight' => 0,
            ]);
        }

        foreach ($package as $cart_item) {
            // Check if cart item is an instance of WC_Order_Item
            // or an array of data from WC_Cart
            if ($cart_item instanceof WC_Order_Item) {
                /** @var WC_Product */
                $product = $cart_item->get_product();
            } else {
                /** @var WC_Product */
                $product = $cart_item['data'];
            }

            if ($product->is_virtual()) {
                continue;
            }

            $dimensions = [
                'height' => wc_get_dimension(curiero_string_to_float($product->get_height() ?: 0), 'cm'),
                'length' => wc_get_dimension(curiero_string_to_float($product->get_length() ?: 0), 'cm'),
                'width' => wc_get_dimension(curiero_string_to_float($product->get_width() ?: 0), 'cm'),
            ];

            // Find smallest dimension
            asort($dimensions);
            $smallestDimension = array_key_first($dimensions);

            // Multiply smallest dimension by quantity
            $dimensions[$smallestDimension] *= $cart_item['quantity'];

            // Add weight to total weight
            $weight = wc_get_weight(curiero_string_to_float($product->get_weight() ?: 0), 'kg');
            $cartWeight += $weight * $cart_item['quantity'];

            // Add product dimensions to cart dimensions
            $cartProductDimensions[] = $dimensions;

            // Sum up dimensions for finding the smallest side later
            foreach ($cartDimensionsSum as $key => $value) {
                $cartDimensionsSum[$key] += $dimensions[$key];
            }
        }

        // Set cart dimensions to 0 by default
        $cartDimensions = ['height' => 0, 'length' => 0, 'width' => 0];

        // Find the smallest side of the items for stacking
        $smallestCartDimensionSide = array_keys($cartDimensionsSum, min($cartDimensionsSum))[0];

        // Set one of the cart dimensions to the sum of the smallest side
        // and the max of the other sides
        foreach ($cartDimensions as $key => $value) {
            if ($key === $smallestCartDimensionSide) {
                $cartDimensions[$key] = $cartDimensionsSum[$key];
            } else {
                $cartDimensions[$key] = max(array_column($cartProductDimensions, $key));
            }
        }

        // Set cart weight to the sum of all products or volumetric weight (LxWxH / 6000)
        $volumetricWeight = array_product($cartDimensions) / 6000;
        $cartDimensions['weight'] = max($cartWeight, $volumetricWeight);

        return apply_filters('curiero_calculate_package_dimensions', [
            'length' => max((int) round($cartDimensions['length']), apply_filters('curiero_fallback_package_length', 1)),
            'width' => max((int) round($cartDimensions['width']), apply_filters('curiero_fallback_package_width', 1)),
            'height' => max((int) round($cartDimensions['height']), apply_filters('curiero_fallback_package_height', 1)),
            'weight' => max((int) round($cartDimensions['weight']), apply_filters('curiero_fallback_package_weight', 1)),
        ], $package);
    }
}

if (!function_exists('curiero_has_free_shipping_coupon')) {
    /**
     * Check if cart has valid free shipping coupon.
     *
     * @return bool
     */
    function curiero_cart_has_free_shipping_coupon(): bool
    {
        return collect(
            WC()->cart->get_coupons()
        )->contains(
            function (WC_Coupon $coupon): bool {
                return $coupon->is_valid() && $coupon->get_free_shipping();
            }
        );
    }
}

<?php

use Automattic\WooCommerce\Utilities\FeaturesUtil;

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Additional_Hooks
{
    /**
     * CurieRO_Additional_Hooks constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('before_woocommerce_init', [$this, 'declare_feature_compatibility']);
        add_filter('woocommerce_package_rates', [$this, 'woocommerce_rates_order']);
        add_action('restrict_manage_posts', [$this, 'display_admin_shop_order_by_meta_filter']);
        add_filter('request', [$this, 'process_admin_shop_order_by_meta'], 99);
        add_filter('woocommerce_shop_order_search_fields', [$this, 'search_by_awb_number']);
        add_filter('woocommerce_checkout_fields', [$this, 'unset_postcode_field']);
        add_filter('woocommerce_formatted_address_replacements', [$this, 'remove_postcode_from_address'], 10, 2);
        add_filter('woocommerce_get_country_locale_base', [$this, 'postcode_defaults_edit']);
        add_action('woocommerce_checkout_update_order_review', [$this, 'clean_package_cache']);
        add_filter('woocommerce_default_address_fields', [$this, 'change_fields_priority']);
        add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
        add_action('woocommerce_order_before_calculate_totals', [$this, 'admin_recalculate_shipping'], 10, 2);
        add_filter('woocommerce_shipping_calculator_enable_postcode', [$this, 'shipping_calc_enable_postcode']);
        add_action('woocommerce_order_status_changed', [$this, 'autogenerate_awb'], 99, 3);
        add_filter('woocommerce_screen_ids', [$this, 'add_curiero_screen_ids']);
        add_filter('curiero_awb_details_overwrite', [$this, 'add_domain_to_awb_request'], 99, 1);
        add_filter(
            CurieRO()->woocommerce_hpos_enabled ? 'woocommerce_order_query_args' : 'woocommerce_get_wp_query_args',
            [$this, 'curiero_order_meta_query']
        );
    }

    /**
     * Declare feature compatibility with Wordpress and WooCommerce.
     *
     * @return void
     */
    public function declare_feature_compatibility(): void
    {
        if (class_exists(FeaturesUtil::class)) {
            // HPOS compatibility
            FeaturesUtil::declare_compatibility('custom_order_tables', CURIERO_PLUGIN_FILE, true);

            // Product Block Editor compatibility
            FeaturesUtil::declare_compatibility('product_block_editor', CURIERO_PLUGIN_FILE, true);

            // Cart & Checkout Blocks compatibility
            FeaturesUtil::declare_compatibility('cart_checkout_blocks', CURIERO_PLUGIN_FILE, false);
        }
    }

    /**
     * Sort shipping methods order based on the specified order.
     *
     * @param array|null $rates
     * @return array
     */
    public function woocommerce_rates_order(?array $rates = []): array
    {
        $selected_order = get_option('curiero_shipping_methods_order', '');
        if (empty($selected_order)) {
            return $rates;
        }

        $sorted_shipping_methods = [];
        $selected_order = explode(',', $selected_order);

        foreach ($selected_order as $rate_method_id) {
            foreach ($rates as $rate) {
                if ($rate->get_method_id() === $rate_method_id) {
                    $sorted_shipping_methods[$rate->get_id()] = $rate;
                }
            }
        }

        return array_merge(array_diff_key($rates, $sorted_shipping_methods), $sorted_shipping_methods);
    }

    /**
     * Display the filter dropdown for orders by option.
     *
     * @return void
     */
    public function display_admin_shop_order_by_meta_filter(): void
    {
        if (!curiero_is_shop_order_edit_screen()) {
            return;
        }

        $domain = 'curiero-plugin';
        $filter_id = 'filter_shop_order_by_courier';
        $current = $_GET[$filter_id] ?? '';
        $options = $this->get_filter_shop_order_meta($domain);

        if (empty($options)) {
            return;
        }

        echo '<select name="' . $filter_id . '" style="width: max-content;max-width: fit-content;"><option value="">' . __('Filtreaza AWB dupa Curier', $domain) . '</option>';
        foreach ($options as $key => $label) {
            printf(
                '<option value="%s"%s>%s</option>',
                $key,
                selected($key, $current, false),
                $label
            );
        }
        echo '</select>';
    }

    /**
     * Process the filter dropdown for orders by option.
     *
     * @param array $vars
     * @return array
     */
    public function process_admin_shop_order_by_meta(array $vars): array
    {
        global $pagenow, $typenow;
        $filter_id = 'filter_shop_order_by_courier';

        if (empty($_GET[$filter_id])) {
            return $vars;
        }

        if (
            ('edit.php' === $pagenow && 'shop_order' === $typenow)
            || (CurieRO()->woocommerce_hpos_enabled && $typenow === curiero_get_shop_order_screen_id())
        ) {
            $vars['meta_key'] = $_GET[$filter_id];
            $vars['orderby'] = 'meta_value';
        }

        return $vars;
    }

    /**
     * Add AWB number to the list of searchable fields.
     *
     * @param array $meta_keys
     * @return array
     */
    public function search_by_awb_number(array $meta_keys): array
    {
        return array_merge(
            $meta_keys,
            array_keys($this->get_filter_shop_order_meta())
        );
    }

    /**
     * Check if postcode field should be enabled.
     *
     * @return bool
     */
    public function shipping_calc_enable_postcode(): bool
    {
        return !(bool) get_option('disable_zipcode_in_checkout');
    }

    /**
     * Unset postcode field from checkout.
     *
     * @param array $fields
     * @return array
     */
    public function unset_postcode_field(array $fields): array
    {
        if (get_option('disable_zipcode_in_checkout')) {
            unset($fields['billing']['billing_postcode'], $fields['shipping']['shipping_postcode']);
        }

        return $fields;
    }

    /**
     * Make postcode field optional.
     *
     * @param array $defaults
     * @return array
     */
    public function postcode_defaults_edit(array $defaults): array
    {
        if (get_option('disable_zipcode_in_checkout')) {
            $defaults['postcode']['required'] = false;
        }

        return $defaults;
    }

    /**
     * Remove postcode from address.
     *
     * @param array $replacements
     * @return array
     */
    public function remove_postcode_from_address(array $replacements): array
    {
        if (get_option('disable_zipcode_in_checkout')) {
            $replacements['{postcode}'] = '';
        }

        return $replacements;
    }

    /**
     * Clean package cache.
     *
     * @return void
     */
    public function clean_package_cache(): void
    {
        if (method_exists('WC_Cache_Helper', 'get_transient_version')) {
            WC_Cache_Helper::get_transient_version('shipping', true);
        } else {
            foreach (array_keys(WC()->cart->get_shipping_packages()) as $key) {
                unset(WC()->session->{"shipping_for_package_{$key}"});
            }
        }
    }

    /**
     * Change fields priority.
     * County should be before city.
     *
     * @param array $fields
     * @return array
     */
    public function change_fields_priority(array $fields): array
    {
        $fields['state']['priority'] = 70;
        $fields['city']['priority'] = 80;

        return $fields;
    }

    /**
     * Autogenerate AWB logic.
     *
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * @return void
     *
     * @throws Exception
     */
    public function autogenerate_awb(int $order_id, string $old_status, string $new_status): void
    {
        $enforce_curiero_methods = apply_filters('curiero_autogenerate_awb_enforce_methods', true);
        $selected_status = apply_filters('curiero_autogenerate_awb_status', 'processing');

        if ($new_status !== $selected_status) {
            return;
        }

        if ($enforce_curiero_methods) {
            foreach (curiero_get_order($order_id)->get_shipping_methods() as $shipping_method) {
                $curiero_tag = curiero_order_item_shipping_tag($shipping_method);
                if (!$curiero_tag) {
                    continue;
                }

                $shipping_method = CurieRO()->printing_methods->get_active()[$curiero_tag] ?? null;
                if (!$shipping_method) {
                    continue;
                }

                $shipping_method::autogenerate_awb($order_id);
            }
        } else {
            foreach (CurieRO()->printing_methods->get_active() as $shipping_method) {
                $shipping_method::autogenerate_awb($order_id);
            }
        }
    }

    /**
     * Recalculate shipping.
     *
     * @param bool|null $and_taxes
     * @param WC_Abstract_Order $order
     * @return void
     */
    public function admin_recalculate_shipping(?bool $and_taxes, WC_Abstract_Order $order): void
    {
        if (($_POST['action'] ?? '') !== 'woocommerce_calc_line_taxes') {
            return;
        }

        $order_shipping_methods = $order->get_shipping_methods();
        if (empty($order_shipping_methods)) {
            return;
        }

        foreach ($order_shipping_methods as $method) {
            try {
                $method_id = $method->get_method_id();
                $_REQUEST['payment_method'] = $order->get_payment_method();

                if (!curiero_order_item_shipping_tag($method)) {
                    continue;
                }

                $calculations = (WC()->shipping->get_shipping_methods()[$method_id])->get_rates_for_package([
                    'destination' => [
                        'country' => isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '',
                        'state' => isset($_POST['state']) ? sanitize_text_field($_POST['state']) : '',
                        'city' => isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '',
                        'postcode' => isset($_POST['postcode']) ? sanitize_text_field($_POST['postcode']) : '',
                        'address' => $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address(),
                    ],
                    'contents' => array_map(function (WC_Order_Item $item): array {
                        return [
                            'quantity' => (int) $item->get_quantity(),
                            'data' => $item->get_product(),
                            'line_total' => $item->get_total(),
                            'line_tax' => $item->get_total_tax(),
                            'line_subtotal' => $item->get_subtotal(),
                            'line_subtotal_tax' => $item->get_subtotal_tax(),
                        ];
                    }, $order->get_items()),
                ]);

                if ($method_id === 'sameday' && $order->meta_exists('curiero_sameday_lockers')) {
                    $method_id = 'curiero_sameday_lockers';
                } elseif ($method_id === 'fan' && $order->meta_exists('curiero_fan_collectpoint')) {
                    $method_id = 'curiero_fan_collectpoint';
                } elseif ($method_id === 'fan' && $order->meta_exists('curiero_fan_fanbox')) {
                    $method_id = 'curiero_fan_fanbox';
                } elseif ($method_id === 'urgentcargus_courier' && $order->meta_exists('curiero_cargus_locker')) {
                    $method_id = 'urgentcargus_courier_ship_and_go';
                } elseif ($method_id === 'dpd' && $order->meta_exists('curiero_dpd_box')) {
                    $method_id = 'curiero_dpd_box';
                } elseif ($method_id === 'innoship' && $order->meta_exists('curiero_innoship_locker')) {
                    $method_id = 'curiero_innoship_locker';
                } elseif ($method_id === 'mygls' && $order->meta_exists('curiero_mygls_box')) {
                    $method_id = 'curiero_mygls_box';
                } elseif ($method_id === 'mygls' && $order->meta_exists('curiero_mygls_collectpoint')) {
                    $method_id = 'curiero_mygls_collectpoint';
                }

                if (isset($calculations[$method_id])) {
                    $method->set_shipping_rate($calculations[$method_id]);
                }
            } catch (Exception $e) {
                error_log("Error {$e->getCode()}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Add curiero meta query for orders.
     *
     * @param array $query
     * @return array
     */
    public function curiero_order_meta_query(array $query): array
    {
        if (!empty($query['curiero_meta'])) {
            $query['meta_query'] = $query['meta_query'] ?? [];
            $query['meta_query'][] = $query['curiero_meta'];
            unset($query['curiero_meta']);
        }

        return $query;
    }

    /**
     * Add domain to AWB request.
     *
     * @param array|null $parameters
     * @return array
     */
    public function add_domain_to_awb_request(?array $parameters = []): array
    {
        return array_merge($parameters, [
            'domain' => site_url(),
        ]);
    }

    /**
     * Add CurieRO screen IDs.
     *
     * @param array $screen_ids
     * @return array
     */
    public function add_curiero_screen_ids(array $screen_ids = []): array
    {
        $curiero_pages = ['toplevel_page_curiero-menu-content', 'curiero_page_curiero-shipping-reorder'];

        if (!is_null(CurieRO()->printing_methods)) {
            foreach (CurieRO()->printing_methods->get_active() as $class) {
                $curiero_pages[] = "curiero_page_{$class::$alias}_settings";
                $curiero_pages[] = "admin_page_{$class::$alias}_generate_awb";
            }
        }

        return array_merge($screen_ids, $curiero_pages);
    }

    /**
     * Get filter shop order meta.
     *
     * @param string $domain
     * @return array
     */
    private function get_filter_shop_order_meta(string $domain = 'curiero-plugin'): array
    {
        $active_classes = [];

        foreach (CurieRO()->printing_methods->get_active() as $class) {
            $active_classes[$class::$awb_field] = __($class::$public_name, $domain);
        }

        return $active_classes;
    }
}

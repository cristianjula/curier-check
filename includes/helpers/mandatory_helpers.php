<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!function_exists('curiero_build_url')) {
    /**
     * Build an URL in admin section with the given path and arguments.
     *
     * @param string $path
     * @param array $args
     * @return string
     */
    function curiero_build_url(string $path, array $args = []): string
    {
        return add_query_arg($args, self_admin_url($path));
    }
}

if (!function_exists('curiero_order_action_url')) {
    /**
     * Build an URL for an order action.
     *
     * @param string $courier
     * @param string $action
     * @param string $order_id
     * @return string
     */
    function curiero_order_action_url(string $courier, string $action, string $order_id)
    {
        return curiero_build_url('admin.php', [
            'page' => 'curiero-order-actions',
            'action' => $action,
            'order_id' => $order_id,
            'courier' => $courier,
            '_wpnonce' => wp_create_nonce("curiero_{$action}_awb_{$order_id}"),
        ]);
    }
}

if (!function_exists('curiero_is_woocommerce_active')) {
    /**
     * Check if WooCommerce is active.
     *
     * @return bool
     */
    function curiero_is_woocommerce_active(): bool
    {
        $plugin = 'woocommerce/woocommerce.php';

        return class_exists('WooCommerce')
            || in_array($plugin, (array) get_option('active_plugins', []), true)
            || (is_multisite() && array_key_exists($plugin, (array) get_site_option('active_sitewide_plugins', [])));
    }
}

if (!function_exists('curiero_make_request')) {
    /**
     * Make a request to the given URL.
     *
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @param float|int $timeout
     * @param bool $json_post
     * @return array|WP_Error
     */
    function curiero_make_request(
        string $url,
        string $method = 'GET',
        array $parameters = [],
        array $headers = [],
        float $timeout = 5,
        bool $json_post = true
    ) {
        if ($method === 'POST' && $json_post) {
            $parameters = json_encode($parameters);
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($parameters),
            ], $headers);
        }

        return wp_remote_request($url, [
            'method' => $method,
            'body' => $parameters,
            'headers' => $headers,
            'timeout' => $timeout,
            'data_format' => 'body',
            'sslverify' => apply_filters('curiero_make_request_sslverify', true),
        ]);
    }
}

if (!function_exists('curiero_manage_options_capability')) {
    /**
     * Get the capability required to manage CurieRO options.
     *
     * @return string
     */
    function curiero_manage_options_capability(): string
    {
        return (string) apply_filters('curiero_manage_options_capability', 'curiero_can_manage_options');
    }
}

if (!function_exists('curiero_woocommerce_hpos_enabled')) {
    /**
     * Check if WooCommerce HPOS is enabled.
     *
     * @return bool
     */
    function curiero_woocommerce_hpos_enabled(): bool
    {
        return method_exists(OrderUtil::class, 'custom_orders_table_usage_is_enabled')
            && OrderUtil::custom_orders_table_usage_is_enabled();
    }
}

if (!function_exists('curiero_get_shop_order_screen_id')) {
    /**
     * Get the Shop Order screen ID.
     *
     * @return string
     */
    function curiero_get_shop_order_screen_id(): string
    {
        return CurieRO()->woocommerce_hpos_enabled
            ? 'woocommerce_page_wc-orders'
            : 'shop_order';
    }
}

if (!function_exists('curiero_is_shop_order_edit_screen')) {
    /**
     * Check if the current screen is the Shop Order edit screen.
     *
     * @return bool
     */
    function curiero_is_shop_order_edit_screen(): bool
    {
        $screen = get_current_screen();
        $expected_screen_id = curiero_get_shop_order_screen_id();

        if (CurieRO()->woocommerce_hpos_enabled) {
            return $screen->base === $expected_screen_id;
        }

        return $screen->post_type === $expected_screen_id
            && in_array($screen->base, ['post', 'edit']);
    }
}

if (!function_exists('curiero_get_api_url')) {
    /**
     * Build a CurieRO API URL.
     *
     * @param string $path
     * @return string
     */
    function curiero_get_api_url(string $path): string
    {
        $domain = trailingslashit(CURIERO_API_URL);
        $path = ltrim($path, '/');

        return "{$domain}{$path}";
    }
}

if (!function_exists('curiero_get_order')) {
    /**
     * Get the WC_Order object from the given post.
     *
     * @param mixed $post
     * @return WC_Abstract_Order
     *
     * @throws Exception
     */
    function curiero_get_order($post): WC_Abstract_Order
    {
        if ($post instanceof WC_Abstract_Order) {
            $order = $post;
        } elseif ($post instanceof WP_Post) {
            $order = wc_get_order($post->ID);
        } elseif (is_numeric($post)) {
            $order = wc_get_order($post);
        }

        if (!$order instanceof WC_Abstract_Order) {
            wp_die(__('CurieRO Error: The selected Post is not an Order.', 'curiero-plugin'));
        }

        return $order;
    }
}

if (!function_exists('curiero_check_nonce_capability')) {
    /**
     * Check the nonce and the capability.
     *
     * @param string $action
     * @param string $capability
     * @return void
     */
    function curiero_check_nonce_capability(string $action, string $capability): void
    {
        if ((bool) apply_filters('curiero_skip_security_checks', false)) {
            return;
        }

        if (
            !check_ajax_referer($action, '_wpnonce', false)
            || !current_user_can($capability)
        ) {
            wp_die(__('CurieRO Error: You are not allowed to perform this action.', 'curiero-plugin'), 403);
        }
    }
}

if (!function_exists('curiero_is_session_shipping_method')) {
    /**
     * Check if the given shipping method is the selected one this session.
     *
     * @param string $shipping_method
     * @return bool
     */
    function curiero_is_session_shipping_method(string $shipping_method): bool
    {
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        if (empty($chosen_shipping_methods)) {
            return false;
        }

        return in_array($shipping_method, $chosen_shipping_methods);
    }
}

if (!function_exists('curiero_string_contains_array_element')) {
    /**
     * Check if the given string contains any of the given array elements.
     *
     * @param string $string
     * @param array $array
     * @return bool
     */
    function curiero_string_contains_array_element(string $string, array $array): bool
    {
        foreach ($array as $item) {
            if (str_contains($string, $item)) {
                return true;
            }
        }

        return false;
    }
}

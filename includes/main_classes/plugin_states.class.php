<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_States
{
    /**
     * Handle plugin activate.
     *
     * @return void
     */
    public static function activate(): void
    {
        // Check WooCommerce dependency
        static::check_woocommerce_dependency();

        // Create plugin database
        CurieRO_Plugin_Database::create_database();

        // Add plugin permissions
        CurieRO_Plugin_Permissions::add_plugin_permissions();
    }

    /**
     * Handle plugin deactivate.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        // Remove plugin database
        CurieRO_Plugin_Database::drop_database();

        // Remove action scheduler actions
        static::remove_as_actions();

        // Remove transients
        static::remove_transients();

        // Clear container cache
        static::clear_container_cache();

        // Remove plugin permissions
        CurieRO_Plugin_Permissions::remove_plugin_permissions();

        // Clear cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

    /**
     * Remove all plugin action scheduler actions.
     *
     * @return void
     */
    public static function remove_as_actions(): void
    {
        if (function_exists('as_unschedule_all_actions') === false) {
            return;
        }

        as_unschedule_all_actions('curiero_gls_awb_update');
        as_unschedule_all_actions('curiero_mygls_awb_update');
        as_unschedule_all_actions('curiero_mygls_awb_cleanup');
        as_unschedule_all_actions('curiero_fan_courier_awb_update');
        as_unschedule_all_actions('curiero_cargus_awb_update');
        as_unschedule_all_actions('curiero_dpd_awb_update');
        as_unschedule_all_actions('curiero_sameday_awb_update');
        as_unschedule_all_actions('curiero_memex_awb_update');
        as_unschedule_all_actions('curiero_optimus_awb_update');
        as_unschedule_all_actions('curiero_express_awb_update');
        as_unschedule_all_actions('curiero_team_awb_update');
        as_unschedule_all_actions('curiero_bookurier_awb_update');
        as_unschedule_all_actions('curiero_innoship_awb_update');

        as_unschedule_all_actions('curiero_memex_call_pickup');
        as_unschedule_all_actions('curiero_fetch_dpd_box');
        as_unschedule_all_actions('curiero_fetch_sameday_easybox');
        as_unschedule_all_actions('curiero_fetch_cargus_lockers');
        as_unschedule_all_actions('curiero_fetch_fan_box');
        as_unschedule_all_actions('curiero_fetch_mygls_box');
        as_unschedule_all_actions('curiero_sameday_lockers');
    }

    /**
     * Remove all plugin transients.
     *
     * @return void
     */
    public static function remove_transients(): void
    {
        if (function_exists('delete_transient') === false) {
            return;
        }

        // General
        delete_transient('curiero_update_plugin');
        // DPD
        delete_transient('curiero_dpd_sender_list');
        delete_transient('curiero_dpd_service_list');
        delete_transient('curiero_dpd_box_list');
        // Innoship
        delete_transient('curiero_innoship_locations');
        delete_transient('curiero_innoship_client_couriers');
        // Cargus
        delete_transient('curiero_cargus_token');
        delete_transient('curiero_cargus_pudo_list');
        // MyGLS
        delete_transient('curiero_mygls_locker_list');
        // Fan Courier
        delete_transient('curiero_fan_token');
        delete_transient('curiero_fan_client_ids');
        delete_transient('curiero_fan_services');
        delete_transient('curiero_fan_collectpoint_list');
        delete_transient('curiero_fan_fanbox_list');
        // Sameday
        delete_transient('curiero_sameday_pickup_points');
        delete_transient('curiero_sameday_services');
        delete_transient('curiero_sameday_locker_list');

        if (class_exists('CurieRO\Sameday\SamedayClient')) {
            $key_token = CurieRO\Sameday\SamedayClient::KEY_TOKEN;
            $key_expires_token = CurieRO\Sameday\SamedayClient::KEY_TOKEN_EXPIRES;

            delete_transient("curiero_sameday_persistent_{$key_token}");
            delete_transient("curiero_sameday_persistent_{$key_expires_token}");
        }

        // Delete expired transients
        if (function_exists('delete_expired_transients')) {
            delete_expired_transients();
        }
    }

    public static function clear_container_cache(): void
    {
        if (!function_exists('WP_Filesystem')) {
            require ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;

        if ($wp_filesystem->is_dir(CURIERO_PLUGIN_PATH . 'cache')) {
            $wp_filesystem->rmdir(CURIERO_PLUGIN_PATH . 'cache', true);
        }
    }

    /**
     * Check WooCommerce dependency.
     *
     * @return void
     */
    public static function check_woocommerce_dependency(): void
    {
        if (CurieRO()->loader->check_woocommerce_dependency()) {
            return;
        }

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!function_exists('deactivate_plugins')) {
            return;
        }

        deactivate_plugins(plugin_basename(CURIERO_PLUGIN_FILE));

        wp_die(
            __('Modulul <b>WooCommerce</b> trebuie sa fie instalat si activ pentru a folosi modulul <b>CurieRO</b>.', 'curiero-plugin')
        );
    }
}

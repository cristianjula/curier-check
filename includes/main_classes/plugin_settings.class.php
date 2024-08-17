<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Settings
{
    /**
     * CurieRO_Settings constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'register_settings']);

        // Add plugin permissions for the options pages
        add_filter('option_page_capability_curiero_settings', 'curiero_manage_options_capability');
        add_filter('option_page_capability_curiero_shipping_methods_order', 'curiero_manage_options_capability');
    }

    /**
     * Register plugin settings.
     *
     * @return void
     */
    public function register_settings(): void
    {
        // FanCourier
        add_option('enable_fan_print', '0');
        add_option('enable_fan_shipping', '0');
        register_setting('curiero_settings', 'enable_fan_print');
        register_setting('curiero_settings', 'enable_fan_shipping');

        // Cargus
        add_option('enable_cargus_print', '0');
        add_option('enable_cargus_shipping', '0');
        register_setting('curiero_settings', 'enable_cargus_print');
        register_setting('curiero_settings', 'enable_cargus_shipping');

        // GLS
        add_option('enable_gls_print', '0');
        add_option('enable_gls_shipping', '0');
        register_setting('curiero_settings', 'enable_gls_print');
        register_setting('curiero_settings', 'enable_gls_shipping');

        // MyGLS
        add_option('enable_mygls_print', '0');
        add_option('enable_mygls_shipping', '0');
        register_setting('curiero_settings', 'enable_mygls_print');
        register_setting('curiero_settings', 'enable_mygls_shipping');

        // DPD
        add_option('enable_dpd_print', '0');
        add_option('enable_dpd_shipping', '0');
        register_setting('curiero_settings', 'enable_dpd_print');
        register_setting('curiero_settings', 'enable_dpd_shipping');

        // Sameday
        add_option('enable_sameday_print', '0');
        add_option('enable_sameday_shipping', '0');
        register_setting('curiero_settings', 'enable_sameday_print');
        register_setting('curiero_settings', 'enable_sameday_shipping');

        // Innoship
        add_option('enable_innoship_print', '0');
        add_option('enable_innoship_shipping', '0');
        register_setting('curiero_settings', 'enable_innoship_print');
        register_setting('curiero_settings', 'enable_innoship_shipping');

        // Bookurier
        add_option('enable_bookurier_print', '0');
        add_option('enable_bookurier_shipping', '0');
        register_setting('curiero_settings', 'enable_bookurier_print');
        register_setting('curiero_settings', 'enable_bookurier_shipping');

        // Memex
        add_option('enable_memex_print', '0');
        add_option('enable_memex_shipping', '0');
        register_setting('curiero_settings', 'enable_memex_print');
        register_setting('curiero_settings', 'enable_memex_shipping');

        // Optimus
        add_option('enable_optimus_print', '0');
        add_option('enable_optimus_shipping', '0');
        register_setting('curiero_settings', 'enable_optimus_print');
        register_setting('curiero_settings', 'enable_optimus_shipping');

        // Express
        add_option('enable_express_print', '0');
        add_option('enable_express_shipping', '0');
        register_setting('curiero_settings', 'enable_express_print');
        register_setting('curiero_settings', 'enable_express_shipping');

        // Team
        add_option('enable_team_print', '0');
        add_option('enable_team_shipping', '0');
        register_setting('curiero_settings', 'enable_team_print');
        register_setting('curiero_settings', 'enable_team_shipping');

        // CurieRO
        add_option('user_curiero', '');
        add_option('password_curiero', '');
        add_option('auth_validity', '0');
        add_option('enable_checkout_city_select', '0');
        add_option('disable_zipcode_in_checkout', '0');

        register_setting('curiero_settings', 'user_curiero');
        register_setting('curiero_settings', 'password_curiero');
        register_setting('curiero_settings', 'auth_validity');
        register_setting('curiero_settings', 'enable_checkout_city_select');
        register_setting('curiero_settings', 'disable_zipcode_in_checkout');

        // Shipping methods order
        add_option('curiero_shipping_methods_order', '');
        register_setting('curiero_shipping_methods_order', 'curiero_shipping_methods_order');

        // CurieRO DB
        add_option('CURIERO_DB_VER', CURIERO_DB_VER);
        add_option('CURIERO_INTERNALS_VER', CURIERO_INTERNALS_VER);
        add_option('curiero_initial_user_report', '0');

        // SmartBill Invoice Generate
        add_option('enable_automatic_smartbill', '0');
        register_setting('curiero_settings', 'enable_automatic_smartbill');

        // Oblio Invoice Generate
        add_option('enable_automatic_oblio', '0');
        register_setting('curiero_settings', 'enable_automatic_oblio');

        // FGO Invoice Generate
        add_option('enable_automatic_fgo', '0');
        register_setting('curiero_settings', 'enable_automatic_fgo');

        // Facturare Pers Fizica/Juridica
        add_option('enable_pers_fiz_jurid', '0');
        register_setting('curiero_settings', 'enable_pers_fiz_jurid');
    }
}

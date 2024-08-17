<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Methods_Loader
{
    /**
     * Active printing methods.
     *
     * @var array
     */
    protected $active = [];

    /**
     * CurieRO_Printing_Methods_Loader constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (!CurieRO()->is_valid_auth) {
            return;
        }

        $this->load_hooks();
        $this->load_couriers();
    }

    /**
     * Get active printing methods.
     *
     * @return array
     */
    public function get_active(): array
    {
        return $this->active;
    }

    /**
     * Set active printing method.
     *
     * @param string $courier_class
     * @return void
     */
    public function set_active(string $courier_class): void
    {
        $courier_class = apply_filters('curiero_before_printing_method_load', $courier_class);

        if (
            !class_exists($courier_class)
            || !property_exists($courier_class, 'alias')
            || isset($this->active[$courier_class::$alias])
        ) {
            return;
        }

        $this->active[$courier_class::$alias] = CurieRO()->container->get($courier_class);
    }

    /**
     * Add printing admin JavaScript.
     *
     * @return void
     */
    public function add_printing_admin_js(): void
    {
        if (!curiero_is_shop_order_edit_screen()) {
            return;
        }

        wp_enqueue_script(
            'curiero_printing_filesaver',
            CURIERO_PLUGIN_URL . 'includes/print_methods/assets/js/filesaver.min.js',
            [],
            '2.1.0'
        );

        wp_enqueue_script(
            'curiero_printing_helpers',
            CURIERO_PLUGIN_URL . 'includes/print_methods/assets/js/awb_printing_helpers.js',
            ['jquery', 'curiero_printing_filesaver'],
            '1.1.3'
        );

        wp_localize_script(
            'curiero_printing_helpers',
            'curiero_ajax_helper',
            [
                '_wpnonce' => wp_create_nonce('curiero_printing_ajax_nonce'),
                'loading_icon' => CURIERO_PLUGIN_URL . 'includes/print_methods/assets/img/loading.gif',
            ]
        );
    }

    /**
     * Add printing admin CSS.
     *
     * @return void
     */
    public function add_printing_admin_css(): void
    {
        if (!curiero_is_shop_order_edit_screen()) {
            return;
        }

        wp_enqueue_style('curiero_order_view_css', plugin_dir_url(__FILE__) . 'assets/css/order_view.min.css', [], '1.0.3');
        wp_enqueue_style('curiero_order_dashboard_css', plugin_dir_url(__FILE__) . 'assets/css/order_dashboard.min.css', [], '1.0.3');
    }

    /**
     * Add action scheduler removal triggers.
     *
     * @return void
     */
    public function remove_as_actions(): void
    {
        add_filter('pre_update_option_enable_fan_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_cargus_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_gls_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_mygls_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_dpd_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_sameday_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_innoship_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_bookurier_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_memex_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_optimus_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_express_print', [$this, 'register_remove_as_actions'], 10, 2);
        add_filter('pre_update_option_enable_team_print', [$this, 'register_remove_as_actions'], 10, 2);
    }

    /**
     * Action scheduler removal action.
     *
     * @param string $new_val
     * @param string $old_val
     * @return string
     */
    public function register_remove_as_actions(string $new_val, string $old_val): string
    {
        if ($old_val === '1' && $new_val === '0') {
            CurieRO_Plugin_States::remove_as_actions();
        }

        return $new_val;
    }

    /**
     * Generate AWB via AJAX.
     *
     * @return void
     */
    public function admin_dashboard_ajax(): void
    {
        curiero_check_nonce_capability(
            'curiero_printing_ajax_nonce',
            'curiero_can_generate_awb'
        );

        $courier = sanitize_text_field($_POST['courier']);

        if (!array_key_exists($courier, $this->active)) {
            wp_send_json_error('Invalid courier sent or insufficient permissions.');
        }

        $this->active[$courier]::generate_awb($_POST['order_id'], true);
        wp_send_json_success();
    }

    /**
     * Register bulk order filters.
     *
     * @return void
     */
    public function register_bulk_order_filters(): void
    {
        $screen_id = curiero_get_shop_order_screen_id();
        if (CurieRO()->woocommerce_hpos_enabled) {
            add_filter("bulk_actions-{$screen_id}", [$this, 'add_bulk_order_options'], 20, 1);
        } else {
            add_filter("bulk_actions-edit-{$screen_id}", [$this, 'add_bulk_order_options'], 20, 1);
        }
    }

    /**
     * Add bulk order options.
     *
     * @param array $bulk_options
     * @return array
     */
    public function add_bulk_order_options(array $bulk_options = []): array
    {
        return array_merge($bulk_options, [
            'bulkSendEmails' => 'Trimite Email cu AWB-ul generat',
            'bulkDownloadAWB' => 'Descarca AWB-ul generat',
        ]);
    }

    /**
     * Load hooks.
     *
     * @return void
     */
    protected function load_hooks(): void
    {
        add_action('admin_init', [$this, 'remove_as_actions']);
        add_action('admin_init', [$this, 'register_bulk_order_filters']);
        add_action('admin_head', [$this, 'add_printing_admin_css']);
        add_action('admin_footer', [$this, 'add_printing_admin_js']);
        add_action('wp_ajax_curiero_generate_awb', [$this, 'admin_dashboard_ajax']);
    }

    /**
     * Load couriers.
     *
     * @return void
     */
    protected function load_couriers(): void
    {
        do_action('curiero_before_printing_methods_load', $this);

        if (get_option('enable_fan_print')) {
            include_once 'fancourier/initialize.php';
            $this->set_active(CurieRO_Printing_Fan::class);
        }

        if (get_option('enable_cargus_print')) {
            include_once 'cargus/initialize.php';
            $this->set_active(CurieRO_Printing_Cargus::class);
        }

        if (get_option('enable_gls_print')) {
            include_once 'gls/initialize.php';
            $this->set_active(CurieRO_Printing_GLS::class);
        }

        if (get_option('enable_mygls_print')) {
            include_once 'mygls/initialize.php';
            $this->set_active(CurieRO_Printing_MyGLS::class);
        }

        if (get_option('enable_dpd_print')) {
            include_once 'dpd/initialize.php';
            $this->set_active(CurieRO_Printing_DPD::class);
        }

        if (get_option('enable_sameday_print')) {
            include_once 'sameday/initialize.php';
            $this->set_active(CurieRO_Printing_Sameday::class);
        }

        if (get_option('enable_innoship_print')) {
            include_once 'innoship/initialize.php';
            $this->set_active(CurieRO_Printing_Innoship::class);
        }

        if (get_option('enable_bookurier_print')) {
            include_once 'bookurier/initialize.php';
            $this->set_active(CurieRO_Printing_Bookurier::class);
        }

        if (get_option('enable_memex_print')) {
            include_once 'memex/initialize.php';
            $this->set_active(CurieRO_Printing_Memex::class);
        }

        if (get_option('enable_optimus_print')) {
            include_once 'optimus/initialize.php';
            $this->set_active(CurieRO_Printing_Optimus::class);
        }

        if (get_option('enable_express_print')) {
            include_once 'express/initialize.php';
            $this->set_active(CurieRO_Printing_Express::class);
        }

        if (get_option('enable_team_print')) {
            include_once 'team/initialize.php';
            $this->set_active(CurieRO_Printing_Team::class);
        }
    }
}

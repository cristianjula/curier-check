<?php

class CurieRO_PFPJ
{
    protected $loader;

    public function __construct()
    {
        $this->loader = new CurieRO_PFPJ_Hooks_Loader();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    public function run(): void
    {
        $this->loader->run();
    }

    public function load_plugin_textdomain(): void
    {
        load_plugin_textdomain(
            'curiero_pf_pj',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/',
        );
    }

    private function set_locale(): void
    {
        $this->loader->add_action('plugins_loaded', $this, 'load_plugin_textdomain', 20);
    }

    private function define_admin_hooks(): void
    {
        $admin_settings = CurieRO()->container->get(CurieRO_PFPJ_Admin_Settings::class);

        $this->loader->add_filter('woocommerce_get_settings_pages', $admin_settings, 'curiero_settings_page_class');
        $this->loader->add_filter('wc_admin_page_tab_sections', $admin_settings, 'register_curiero_admin_tabs');

        // Save Order Meta
        $this->loader->add_action('woocommerce_checkout_update_order_meta', $admin_settings, 'update_order_meta');

        // Save extra fields to customer
        $this->loader->add_action('woocommerce_checkout_update_user_meta', $admin_settings, 'update_customer_data', 10, 2);

        // Filter billing fields
        $this->loader->add_filter('woocommerce_order_formatted_billing_address', $admin_settings, 'filter_billing_fields', PHP_INT_MAX, 2);
        $this->loader->add_filter('woocommerce_my_account_my_address_formatted_address', $admin_settings, 'myacc_filter_billing_fields', 90, 3);
        $this->loader->add_filter('woocommerce_formatted_address_replacements', $admin_settings, 'extra_fields_replacements', PHP_INT_MAX, 2);
        $this->loader->add_filter('woocommerce_localisation_address_formats', $admin_settings, 'localisation_address_formats', 90);

        // WC Admin
        $this->loader->add_action('admin_menu', $admin_settings, 'wc_admin_connect_page', 15);

        // Admin edit fields
        $this->loader->add_filter('woocommerce_admin_billing_fields', $admin_settings, 'admin_billing_fields', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_curiero_pf_pj_type', $admin_settings, 'admin_billing_get_curiero_pf_pj_type', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_cnp', $admin_settings, 'admin_billing_get_cnp', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_cui', $admin_settings, 'admin_billing_get_cui', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_nume_banca', $admin_settings, 'admin_billing_get_nume_banca', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_nr_reg_com', $admin_settings, 'admin_billing_get_nr_reg_com', 10, 2);
        $this->loader->add_filter('woocommerce_order_get__billing_iban', $admin_settings, 'admin_billing_get_iban', 10, 2);

        // Save admin fields
        $this->loader->add_action('woocommerce_process_shop_order_meta', $admin_settings, 'save_admin_billing_fields', 30);

        // Scripts for conditional fields
        $this->loader->add_action('admin_enqueue_scripts', $admin_settings, 'admin_enqueue_scripts');
    }

    private function define_public_hooks(): void
    {
        $checkout = CurieRO()->container->get(CurieRO_PFPJ_Checkout_Form::class);

        $this->loader->add_action('wp_head', $checkout, 'hide_fields');
        $this->loader->add_action('wp_footer', $checkout, 'add_js_to_footer', 99);

        // Change checkout fields
        $this->loader->add_filter('woocommerce_billing_fields', $checkout, 'override_checkout_fields', 30);
        $this->loader->add_filter('woocommerce_form_field', $checkout, 'override_field_html', 20, 3);
        $this->loader->add_filter('woocommerce_form_field_args', $checkout, 'form_field_checkout_args', 20, 3);
        $this->loader->add_filter('woocommerce_checkout_fields', $checkout, 'make_fields_optional', PHP_INT_MAX);

        // Validate checkout fields
        $this->loader->add_action('woocommerce_checkout_process', $checkout, 'validate_checkout');
        $this->loader->add_action('woocommerce_checkout_create_order', $checkout, 'unset_default_company_field_value', 10, 2);

        // Add fields to profile
        $this->loader->add_filter('woocommerce_address_to_edit', $checkout, 'user_profile_fields', 90, 2);
        // Save fields to profile
        $this->loader->add_filter('woocommerce_customer_save_address', $checkout, 'save_user_profile_fields', 90, 2);
    }
}

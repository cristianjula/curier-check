<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Shipping_Methods_Loader
{
    /**
     * Active shipping methods.
     *
     * @var array
     */
    protected $active = [];

    /**
     * CurieRO_Shipping_Methods_Loader constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (!CurieRO()->is_valid_auth) {
            return;
        }

        $this->load_couriers();
        $this->register_courier_methods();

        add_filter('woocommerce_shipping_chosen_method', [$this, 'hook_previous_shipping_method'], 99, 2);
    }

    /**
     * Get active shipping methods.
     *
     * @return array
     */
    public function get_active(): array
    {
        return $this->active;
    }

    /**
     * Set active shipping method.
     *
     * @param string $shipping_class
     * @param string $printing_class
     * @param string $fallback
     * @return void
     */
    public function set_active(string $shipping_class, string $printing_class, string $fallback): void
    {
        $shipping_class = apply_filters('curiero_before_shipping_method_load', $shipping_class);
        $key = property_exists($printing_class, 'alias') ? $printing_class::$alias : $fallback;

        if (
            !function_exists($shipping_class)
            || !class_exists($printing_class)
            || isset($this->active[$key])
        ) {
            return;
        }

        $this->active[$key] = $shipping_class;
    }

    /**
     * Set previous shipping method based on session.
     *
     * @param string $method
     * @param array $available_methods
     * @return string
     */
    public function hook_previous_shipping_method(string $method, array $available_methods): string
    {
        $current_method = WC()->session->get('chosen_shipping_methods')[0] ?? '';

        if (empty($current_method)) {
            return $method;
        }

        if (isset($available_methods[$current_method])) {
            return $current_method;
        }

        return $method;
    }

    /**
     * Load shipping methods.
     *
     * @return void
     */
    protected function load_couriers(): void
    {
        do_action('curiero_before_shipping_methods_load', $this);

        if (get_option('enable_fan_shipping')) {
            include_once 'fancourier/fan-courier-shipping-calculate.php';
            $this->set_active(Fan_Shipping_Method::class, CurieRO_Printing_Fan::class, 'fancourier');
        }

        if (get_option('enable_cargus_shipping')) {
            include_once 'cargus/cargus-courier-shipping-calculate.php';
            $this->set_active(Cargus_Shipping_Method::class, CurieRO_Printing_Cargus::class, 'cargus');
        }

        if (get_option('enable_gls_shipping')) {
            include_once 'gls/gls-shipping-calculate.php';
            $this->set_active(GLS_Shipping_Method::class, CurieRO_Printing_GLS::class, 'gls');
        }

        if (get_option('enable_mygls_shipping')) {
            include_once 'mygls/mygls-shipping-calculate.php';
            $this->set_active(MyGLS_Shipping_Method::class, CurieRO_Printing_MyGLS::class, 'mygls');
        }

        if (get_option('enable_dpd_shipping')) {
            include_once 'dpd/dpd-shipping-calculate.php';
            $this->set_active(DPD_Shipping_Method::class, CurieRO_Printing_DPD::class, 'dpd');
        }

        if (get_option('enable_sameday_shipping')) {
            include_once 'sameday/sameday-shipping-calculate.php';
            $this->set_active(Sameday_Shipping_Method::class, CurieRO_Printing_Sameday::class, 'sameday');
        }

        if (get_option('enable_innoship_shipping')) {
            include_once 'innoship/innoship-shipping-calculate.php';
            $this->set_active(Innoship_Shipping_Method::class, CurieRO_Printing_Innoship::class, 'innoship');
        }

        if (get_option('enable_bookurier_shipping')) {
            include_once 'bookurier/bookurier-shipping-calculate.php';
            $this->set_active(Bookurier_Shipping_Method::class, CurieRO_Printing_Bookurier::class, 'bookurier');
        }

        if (get_option('enable_memex_shipping')) {
            include_once 'memex/memex-shipping-calculate.php';
            $this->set_active(Memex_Shipping_Method::class, CurieRO_Printing_Memex::class, 'memex');
        }

        if (get_option('enable_optimus_shipping')) {
            include_once 'optimus/optimus-shipping-calculate.php';
            $this->set_active(Optimus_Shipping_Method::class, CurieRO_Printing_Optimus::class, 'optimus');
        }

        if (get_option('enable_express_shipping')) {
            include_once 'express/express-shipping-calculate.php';
            $this->set_active(Express_Shipping_Method::class, CurieRO_Printing_Express::class, 'express');
        }

        if (get_option('enable_team_shipping')) {
            include_once 'team/team-shipping-calculate.php';
            $this->set_active(Team_Shipping_Method::class, CurieRO_Printing_Team::class, 'team');
        }
    }

    /**
     * Register shipping methods.
     *
     * @return void
     */
    protected function register_courier_methods(): void
    {
        foreach ($this->get_active() as $shipping_class) {
            add_action('woocommerce_shipping_init', $shipping_class);
        }

        add_filter('woocommerce_shipping_methods', function (array $methods = []): array {
            return array_merge($methods, $this->get_active());
        });
    }
}

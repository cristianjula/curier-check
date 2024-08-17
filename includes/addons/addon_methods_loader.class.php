<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Addon_Methods_Loader
{
    /**
     * Active addons.
     *
     * @var array
     */
    protected $active = [];

    /**
     * CurieRO_Addon_Methods_Loader constructor.
     *
     * @return void
     */
    public function __construct()
    {
        if (!CurieRO()->is_valid_auth) {
            return;
        }

        $this->load_addons();
    }

    /**
     * Get active addons.
     *
     * @return array
     */
    public function get_active(): array
    {
        return $this->active;
    }

    /**
     * Set active addon.
     *
     * @param string $addon_class
     * @return void
     */
    public function set_active(string $addon_class): void
    {
        $addon_class = apply_filters('curiero_before_addon_method_load', $addon_class);

        if (
            !class_exists($addon_class)
            || isset($this->active[$addon_class])
        ) {
            return;
        }

        $this->active[$addon_class] = CurieRO()->container->get($addon_class);
    }

    /**
     * Load addons.
     *
     * @return void
     */
    protected function load_addons(): void
    {
        do_action('curiero_before_addon_methods_load', $this);

        if (get_option('enable_pers_fiz_jurid')) {
            include_once 'persoana-fizica-juridica/initialize.php';
            $this->set_active(CurieRO_Addons_PFPJ::class);
        }

        if (
            !empty(CurieRO()->shipping_methods->get_active())
            || get_option('enable_checkout_city_select')
        ) {
            include_once 'city_select/city-select.php';
            $this->set_active(CurieRO_City_Select::class);
        }
    }
}

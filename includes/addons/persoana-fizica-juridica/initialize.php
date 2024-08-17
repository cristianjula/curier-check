<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Addons_PFPJ
{
    public static $alias = 'pfpj';

    public static $public_name = 'Persoana Fizica / Persoana Juridica';

    public function __construct()
    {
        define('CURIERO_PF_PJ_SLUG', plugin_basename(__FILE__));
        define('CURIERO_PF_PJ_ASSETS', plugin_dir_url(__FILE__) . '/assets/');

        add_action('admin_menu', [$this, 'add_plugin_curiero_pf_pj']);
        $this->load_dependencies();

        (new CurieRO_PFPJ())->run();
    }

    public function add_plugin_curiero_pf_pj(): void
    {
        add_submenu_page(
            'curiero-menu-content',
            'Persoana fizica / Persoana juridica',
            'Persoana fizica / Persoana juridica',
            curiero_manage_options_capability(),
            'pf_pj_submenu_content',
            function (): void {
                wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=curiero-pf-pj')); // redirectionez din curiero catre woocommerce settings/pf_pj
            }
        );
    }

    private function load_dependencies(): void
    {
        require 'includes/curiero_pfpj.class.php';
        require 'includes/curiero_hooks_loader.class.php';
        require 'includes/curiero_options.class.php';
        require 'includes/curiero_checkout_form.class.php';
        require 'includes/curiero_validations.php';
        require 'admin/settings.class.php';
    }
}

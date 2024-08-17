<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Admin_Menu
{
    /**
     * CurieRO_Admin_Menu constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }

    /**
     * Register admin menu.
     *
     * @return void
     */
    public function register_admin_menu(): void
    {
        add_menu_page(
            __('CurieRO', 'curiero-plugin'),
            'CurieRO',
            curiero_manage_options_capability(),
            'curiero-menu-content',
            [$this, 'curiero_menu_content'],
            'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMTc5MiAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGZpbGw9IiNhMGE1YWEiIGQ9Ik02NDAgMTQwOHEwLTUyLTM4LTkwdC05MC0zOC05MCAzOC0zOCA5MCAzOCA5MCA5MCAzOCA5MC0zOCAzOC05MHpNMjU2IDg5NmgzODRWNjQwSDQ4MnEtMTMgMC0yMiA5TDI2NSA4NDRxLTkgOS05IDIydjMwem0xMjgwIDUxMnEwLTUyLTM4LTkwdC05MC0zOC05MCAzOC0zOCA5MCAzOCA5MCA5MCAzOCA5MC0zOCAzOC05MHptMjU2LTEwODh2MTAyNHEwIDE1LTQgMjYuNXQtMTMuNSAxOC41LTE2LjUgMTEuNS0yMy41IDYtMjIuNSAyLTI1LjUgMC0yMi41LS41cTAgMTA2LTc1IDE4MXQtMTgxIDc1LTE4MS03NS03NS0xODFINzY4cTAgMTA2LTc1IDE4MXQtMTgxIDc1LTE4MS03NS03NS0xODFoLTY0bC0yMi41LjVxLTE5LjUuNS0yNS41IDB0LTIyLjUtMi0yMy41LTYtMTYuNS0xMS41LTEzLjUtMTguNS00LTI2LjVxMC0yNiAxOS00NXQ0NS0xOVY5NjBxMC04LS41LTM1dDAtMzggMi41LTM0LjUgNi41LTM3IDE0LTMwLjUgMjIuNS0zMGwxOTgtMTk4cTE5LTE5IDUwLjUtMzJ0NTguNS0xM2gxNjBWMzIwcTAtMjYgMTktNDV0NDUtMTloMTAyNHEyNiAwIDQ1IDE5dDE5IDQ1eiIvPjwvc3ZnPg==',
            99
        );

        add_submenu_page(
            'curiero-menu-content',
            __('Setari Generale', 'curiero-plugin'),
            __('Setari Generale', 'curiero-plugin'),
            curiero_manage_options_capability(),
            'curiero-menu-content',
            [$this, 'curiero_menu_content']
        );

        add_submenu_page(
            'curiero-menu-content',
            __('Livrare - Sortare', 'curiero-plugin'),
            __('Livrare - Sortare', 'curiero-plugin'),
            curiero_manage_options_capability(),
            'curiero-shipping-reorder',
            [$this, 'curiero_shipping_reorder_content']
        );
    }

    /**
     * CurieRO menu content.
     *
     * @return void
     */
    public function curiero_menu_content(): void
    {
        wc_get_template(
            'templates/settings_page.php',
            [],
            '',
            plugin_dir_path(__FILE__),
        );
    }

    /**
     * CurieRO shipping reorder content.
     *
     * @return void
     */
    public function curiero_shipping_reorder_content(): void
    {
        wc_get_template(
            'templates/shipping_reorder_page.php',
            [],
            '',
            plugin_dir_path(__FILE__),
        );
    }
}

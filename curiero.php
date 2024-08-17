<?php

/*******************************************************************************
 * Plugin Name: CurieRO
 * Plugin URI: https://curie.ro
 * Description: Plugin-ul CurieRO All-in-one - Generare AWB si Metode de livrare
 * Version: 2.33.1
 * Author: Echipa CurieRO
 * Author URI: https://curie.ro
 * WC requires at least: 3.4.5
 * WC tested up to: 9.1.2
 * Requires PHP: 7.3.0
 * Requires Plugins: woocommerce
 * Text Domain: curiero-plugin
 *******************************************************************************/

// Exit if accessed directly
defined('ABSPATH') || exit;

final class CurieRO
{
    /**
     * The single instance of the class.
     *
     * @var CurieRO
     *
     * @since 2.20.0
     */
    private static $_instance;

    /**
     * The loader that's responsible for maintaining and
     * registering all hooks that power the plugin.
     *
     * @var CurieRO_Plugin_Loader
     *
     * @since 2.20.0
     */
    private $loader;

    /**
     * CurieRO constructor.
     *
     * @since 2.20.0
     */
    private function __construct()
    {
        $this->set_defines();
        $this->register_dependencies();

        $this->loader = CurieRO_Plugin_Loader::instance();
    }

    /**
     * Prevent the instance from being cloned.
     *
     * @since 2.20.0
     *
     * @return void
     */
    private function __clone()
    {
        // ! Do nothing
        throw new Exception('CurieRO class cloning is forbidden.');
    }

    /**
     * Prevent from being unserialized.
     *
     * @since 2.20.0
     *
     * @return void
     */
    public function __wakeup(): void
    {
        // ! Do nothing
        throw new Exception('CurieRO class is not serializable.');
    }

    /**
     * Magic method to get properties.
     *
     * @since 2.20.0
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return property_exists($this, $property)
            ? $this->{$property}
            : $this->loader->{$property};
    }

    /**
     * Main CurieRO Instance.
     *
     * Ensures only one instance of CurieRO is loaded or can be loaded.
     *
     * @since 2.20.0
     *
     * @static
     *
     * @return CurieRO - Main instance.
     */
    public static function instance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Set plugin constants.
     *
     * @since 2.20.0
     *
     * @return string
     */
    private function set_defines(): void
    {
        define('CURIERO_DB_VER', '1.3.0');
        define('CURIERO_INTERNALS_VER', '1.0.10');

        define('CURIERO_PLUGIN_FILE', __FILE__);
        define('CURIERO_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CURIERO_PLUGIN_PATH', plugin_dir_path(__FILE__));

        define('CURIERO_API_URL', 'https://api.curie.ro');
        define('CURIERO_API_VERSION_JSON', 'https://api.curie.ro/plugin/curiero-plugin.json');
    }

    /**
     * Register plugin dependencies.
     *
     * @since 2.20.0
     *
     * @return void
     */
    private function register_dependencies(): void
    {
        require 'vendor/scoper-autoload.php';
        require 'includes/register_plugin_loader.php';
    }
}

/**
 * Returns the main instance of CurieRO plugin.
 *
 * @since 2.19.0
 *
 * @return CurieRO
 */
function CurieRO(): CurieRO
{
    return CurieRO::instance();
}

// Global for backwards compatibility.
$GLOBALS['curiero'] = CurieRO();

<?php

use CurieRO\DI\Container;
use CurieRO\DI\ContainerBuilder;

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_Loader
{
    /**
     * Dependency container instance.
     *
     * @var Container
     *
     * @since 2.30.0
     */
    protected $container;

    /**
     * The single instance of the class.
     *
     * @var CurieRO_Plugin_Loader
     *
     * @since 2.20.0
     */
    private static $_instance;

    /**
     * CurieRO_Plugin_Loader constructor.
     *
     * Load plugin after all plugins are
     * loaded but before every init hook.
     *
     * @since 2.20.0
     *
     * @return void
     */
    public function __construct()
    {
        // Register plugin main classes
        $this->register_main_classes();

        // Register Admin classes (menu items, settings, etc.)
        $this->register_admin_classes();

        // Register plugin request classes (for API requests)
        $this->register_request_classes();

        // Register plugin helpers (mandatory helpers that can't be overwritten)
        $this->register_mandatory_helpers();

        // Regsiter dependency container
        $this->register_container();

        // Register activation and deactivation hooks
        register_activation_hook(CURIERO_PLUGIN_FILE, [CurieRO_Plugin_States::class, 'activate']);
        register_deactivation_hook(CURIERO_PLUGIN_FILE, [CurieRO_Plugin_States::class, 'deactivate']);

        // Check WooCommerce dependency
        if ($this->check_woocommerce_dependency() === false) {
            return;
        }

        // Initialize plugin update modules
        add_action('plugins_loaded', [$this, 'load_update_modules'], 0);

        // Initialize plugin settings
        add_action('before_woocommerce_init', [$this, 'load_settings_modules'], 0);

        // Initialize plugin main modules
        add_action('woocommerce_init', [$this, 'load_main_modules'], -1);
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
        throw new Exception('CurieRO Loader class cloning is forbidden.');
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
        throw new Exception('CurieRO Loader class is not serializable.');
    }

    /**
     * Magic method to get properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        return $this->container->has($property)
            ? $this->container->get($property)
            : null;
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
     * @return CurieRO_Plugin_Loader - Main instance.
     */
    public static function instance(): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    /**
     * Register plugin container.
     *
     * @return void
     */
    public function register_container(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(CURIERO_PLUGIN_PATH . 'container.config.php')
            ->enableCompilation(CURIERO_PLUGIN_PATH . 'cache', 'CurieROCompiledContainer')
            ->writeProxiesToFile(true, CURIERO_PLUGIN_PATH . 'cache/proxies');

        $this->container = $builder->build();
    }

    /**
     * Initialize the plugin update modules.
     *
     * @return void
     */
    public function load_update_modules(): void
    {
        $this->container->get(CurieRO_Plugin_Update::class);
        $this->container->get(CurieRO_Plugin_DB_Update::class);
        $this->container->get(CurieRO_Plugin_Internals_Update::class);
    }

    /**
     * Initialize the plugin settings modules.
     *
     * @return void
     */
    public function load_settings_modules(): void
    {
        $this->container->set('is_valid_auth', (bool) get_option('auth_validity', false));
        $this->container->set('woocommerce_hpos_enabled', curiero_woocommerce_hpos_enabled());

        do_action('before_curiero_init');

        $this->container->get(CurieRO_Settings::class);
        $this->container->get(CurieRO_Additional_Hooks::class);

        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        $this->container->get(CurieRO_Email_Methods::class);
        $this->container->get(CurieRO_Admin_Report::class);
        $this->container->get(CurieRO_Admin_Menu::class);

        do_action('curiero_init');
    }

    /**
     * Initialize the plugin main modules.
     *
     * @return void
     */
    public function load_main_modules(): void
    {
        $this->register_conditional_helpers();

        do_action('before_curiero_loaded');

        $this->load_printing_methods();
        $this->load_shipping_methods();
        $this->load_addon_methods();

        do_action('curiero_loaded');
    }

    /**
     * Check WooCommerce dependency.
     *
     * @return bool
     */
    public function check_woocommerce_dependency(): bool
    {
        if (curiero_is_woocommerce_active()) {
            return true;
        }

        add_action('admin_notices', function (): void {
            echo '<div class="notice notice-error"><p>' . __('Modulul <b>WooCommerce</b> trebuie sa fie instalat si activ pentru a folosi modulul <b>CurieRO</b>.', 'curiero-plugin') . '</p></div>';
        });

        return false;
    }

    /**
     * Load required helpers.
     *
     * @return void
     */
    protected function register_mandatory_helpers(): void
    {
        require CURIERO_PLUGIN_PATH . 'includes/helpers/mandatory_helpers.php';
    }

    /**
     * Load conditional helpers.
     *
     * @return void
     */
    protected function register_conditional_helpers(): void
    {
        require CURIERO_PLUGIN_PATH . 'includes/helpers/conditional_helpers.php';
    }

    /**
     * Load printing methods.
     *
     * @return void
     */
    protected function load_printing_methods(): void
    {
        require CURIERO_PLUGIN_PATH . 'includes/print_methods/curiero_printing_method.abstract.php';
        require CURIERO_PLUGIN_PATH . 'includes/print_methods/printing_methods_loader.class.php';

        $loader = $this->container->get(CurieRO_Printing_Methods_Loader::class);
        $this->container->get(CurieRO_AWB_Actions::class);

        do_action('curiero_printing_methods_loaded', $loader);
    }

    /**
     * Load shipping methods.
     *
     * @return void
     */
    protected function load_shipping_methods(): void
    {
        require CURIERO_PLUGIN_PATH . 'includes/shipping_methods/shipping_methods_loader.class.php';

        $loader = $this->container->get(CurieRO_Shipping_Methods_Loader::class);

        do_action('curiero_shipping_methods_loaded', $loader);
    }

    /**
     * Load addon methods.
     *
     * @return void
     */
    protected function load_addon_methods(): void
    {
        require CURIERO_PLUGIN_PATH . 'includes/addons/addon_methods_loader.class.php';

        $loader = $this->container->get(CurieRO_Addon_Methods_Loader::class);

        do_action('curiero_addon_methods_loaded', $loader);
    }

    /**
     * Register main classes.
     *
     * @return void
     */
    protected function register_main_classes(): void
    {
        array_map(
            function (string $class_path): void {
                require $class_path;
            },
            glob(CURIERO_PLUGIN_PATH . 'includes/main_classes/*.class.php', GLOB_NOSORT)
        );
    }

    /**
     * Register admin classes.
     *
     * @return void
     */
    protected function register_admin_classes(): void
    {
        array_map(
            function (string $class_path): void {
                require $class_path;
            },
            glob(CURIERO_PLUGIN_PATH . 'includes/admin/*.php', GLOB_NOSORT)
        );
    }

    /**
     * Register request classes.
     *
     * @return void
     */
    protected function register_request_classes(): void
    {
        array_map(
            function (string $class_path): void {
                require $class_path;
            },
            array_merge(
                glob(CURIERO_PLUGIN_PATH . 'includes/request_classes/couriers/*.class.php', GLOB_NOSORT),
                glob(CURIERO_PLUGIN_PATH . 'includes/request_classes/curiero/*.class.php', GLOB_NOSORT)
            )
        );
    }
}

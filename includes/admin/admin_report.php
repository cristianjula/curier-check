<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Admin_Report
{
    /**
     * CurieRO_Admin_Report constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('wp_ajax_curiero_send_report', [$this, 'send_report']);
        add_action('update_option_auth_validity', [$this, 'send_report_on_successful_auth'], 99, 2);
    }

    /**
     * Generate report content.
     *
     * @return string
     */
    public static function generate_report()
    {
        $htaccess_path = static::htaccess_search();

        if (is_multisite()) {
            $active_plugins = array_keys(get_site_option('active_sitewide_plugins', []));
        } else {
            $active_plugins = get_option('active_plugins');
        }

        $extras = [
            'wordpress version' => get_bloginfo('version'),
            'woocommerce version' => static::get_wc_version_number(),
            'php version' => phpversion(),
            'siteurl' => get_option('siteurl'),
            'home' => get_option('home'),
            'home_url' => home_url(),
            'locale' => get_locale(),
            'active theme' => wp_get_theme()->get('Name'),
        ];

        $extras['active plugins'] = $active_plugins;

        $item_options = [
            'CURIERO_PLUGIN_VERSION', 'CURIERO_DB_VER', 'CURIERO_INTERNALS_VER', 'WOOCOMMERCE_HPOS_ENABLED', 'user_curiero', 'auth_validity', 'enable_fan_print', 'enable_fan_shipping', 'enable_cargus_print', 'enable_cargus_shipping', 'enable_gls_print', 'enable_mygls_print', 'enable_gls_shipping', 'enable_mygls_shipping', 'enable_dpd_print', 'enable_dpd_shipping', 'enable_sameday_print', 'enable_sameday_shipping', 'enable_innoship_print', 'enable_innoship_shipping', 'enable_bookurier_print', 'enable_bookurier_shipping', 'enable_memex_print', 'enable_memex_shipping', 'enable_optimus_print', 'enable_optimus_shipping', 'enable_express_print', 'enable_express_shipping', 'enable_team_print', 'enable_team_shipping', 'enable_checkout_city_select', 'enable_pers_fiz_jurid', 'disable_zipcode_in_checkout', 'enable_automatic_smartbill', 'enable_automatic_oblio', 'enable_automatic_fgo',
        ];

        $options = [];
        foreach ($item_options as $v) {
            if ($v === 'CURIERO_PLUGIN_VERSION') {
                $options[$v] = get_file_data(CURIERO_PLUGIN_FILE, ['Version' => 'Version'], 'plugin')['Version'];
            } elseif ($v === 'WOOCOMMERCE_HPOS_ENABLED') {
                $options[$v] = (int) CurieRO()->woocommerce_hpos_enabled;
            } else {
                $options[$v] = get_option($v, '0');
            }
        }

        return static::build_report($options, $extras, $htaccess_path);
    }

    /**
     * Send report.
     *
     * @param bool $plainResponse
     * @return mixed
     */
    public static function send_report(bool $plainResponse = true)
    {
        $request = curiero_make_request(
            curiero_get_api_url('/v1/user/report'),
            'POST',
            [
                'api_user' => get_option('user_curiero'),
                'api_pass' => get_option('password_curiero'),
                'report' => self::generate_report(),
            ]
        );

        $wasSuccessful = !is_wp_error($request);

        if ($plainResponse) {
            return $wasSuccessful;
        }

        return wp_send_json(['success' => $wasSuccessful]);
    }

    /**
     * Send report on successful auth.
     *
     * @param string $old_value
     * @param string $new_value
     * @return void
     */
    public static function send_report_on_successful_auth(string $old_value, string $new_value): void
    {
        if (
            $old_value === '0'
            && $new_value === '1'
        ) {
            static::send_report();
        }
    }

    /**
     * Build report content from options, extras and htaccess.
     *
     * @param array|null $options
     * @param array $extras
     * @param string $htaccess_path
     * @return string
     */
    protected static function build_report(?array $options, array $extras = [], string $htaccess_path = '')
    {
        global $_SERVER;

        $server_vars = array_intersect_key($_SERVER, [
            'DOCUMENT_ROOT' => '',
            'SERVER_SOFTWARE' => '',
            'REQUEST_URI' => '',
        ]);

        $buf = static::format_report_section('Server Variables:', $server_vars);
        $buf .= static::format_report_section('Wordpress Specific Extras:', $extras);
        $buf .= static::format_report_section('CurieRO Plugin Options:', $options);

        $buf .= "HTAccess below this point:\n";

        if (empty($htaccess_path) || !file_exists($htaccess_path) || !is_readable($htaccess_path)) {
            $buf .= $htaccess_path . "   File does not exist or is not readable.\n";
        } else {
            $content = file_get_contents($htaccess_path);
            if ($content === false) {
                $buf .= $htaccess_path . "   File returned false for file_get_contents.\n";
            } else {
                $buf .= 'Path: ';
                $buf .= $htaccess_path . "\n" . $content . "\n\n";
            }
        }

        return trim($buf);
    }

    /**
     * Format report section.
     *
     * @param string $section_header
     * @param array|null $section
     * @return string
     */
    protected static function format_report_section(string $section_header, ?array $section)
    {
        if (empty($section)) {
            return 'No matching ' . $section_header . "\n\n";
        }

        $buf = $section_header;
        foreach ($section as $k => $v) {
            $buf .= "\n" . '   ';
            if (!is_numeric($k)) {
                $buf .= $k . ' = ';
            }
            if (!is_string($v)) {
                $v = var_export($v, true);
            }
            $buf .= $v;
        }

        return $buf . "\n\n";
    }

    /**
     * Get frontend path.
     *
     * @return false|string
     */
    protected static function frontend_path()
    {
        $frontend = rtrim(ABSPATH, '/');
        if (!$frontend) {
            $frontend = parse_url(get_option('home'));
            $frontend = !empty($frontend['path']) ? $frontend['path'] : '';
            $frontend = $_SERVER['DOCUMENT_ROOT'] . $frontend;
        }

        return realpath($frontend);
    }

    /**
     * Search for htaccess.
     *
     * @return false|string
     */
    protected static function htaccess_search()
    {
        $max_depth = 0;
        $start_path = static::frontend_path();

        while (!file_exists($start_path . '/.htaccess') && $max_depth < 10) {
            if ($start_path === '/' || !$start_path) {
                return false;
            }
            if (!empty($_SERVER['DOCUMENT_ROOT']) && $start_path === $_SERVER['DOCUMENT_ROOT']) {
                return false;
            }
            $start_path = dirname($start_path);
            ++$max_depth;
        }

        return "{$start_path}/.htaccess";
    }

    /**
     * Get WooCommerce version number.
     *
     * @return string
     */
    protected static function get_wc_version_number(): string
    {
        if (
            function_exists('WC')
            && property_exists(WC(), 'version')
        ) {
            return WC()->version;
        }

        if (!function_exists('get_plugins')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_folder = get_plugins('/woocommerce');
        $plugin_file = 'woocommerce.php';

        return $plugin_folder[$plugin_file]['Version'] ?? 'MISSING';
    }
}

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_Update
{
    /**
     * Plugin basename.
     *
     * @var string
     */
    private $basename = '';

    /**
     * Plugin data.
     *
     * @var array
     */
    private $plugin_data = [];

    /**
     * CurieRO_Plugin_Update constructor.
     */
    public function __construct()
    {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $this->basename = plugin_basename(CURIERO_PLUGIN_FILE);
        $this->plugin_data = get_plugin_data(CURIERO_PLUGIN_FILE, false, false);

        add_filter('plugin_row_meta', [$this, 'handle_row_meta'], 10, 2);
        add_filter('plugins_api', [$this, 'handle_register_api'], 20, 3);
        add_filter("plugin_action_links_{$this->basename}", [$this, 'action_links']);
        add_action('site_transient_update_plugins', [$this, 'handle_site_transient_update'], 10, 1);
        add_action('upgrader_process_complete', [$this, 'handle_update_process_complete'], 10, 2);
        add_action('core_upgrade_preamble', [$this, 'remove_curiero_update_plugin_transient'], 10, 0);
    }

    /**
     * Add the plugin meta links.
     *
     * @param array $plugin_meta
     * @param string $pluginFile
     * @return array
     */
    public function handle_row_meta(array $plugin_meta, string $pluginFile): array
    {
        if ($this->basename === $pluginFile) {
            $plugin_meta[] = sprintf(
                '<a href="%s" class="open-plugin-documentation" aria-label="%s" data-title="%s" target="_blank">%s</a>',
                esc_url('https://curie.ro/documentatie/'),
                esc_attr(__('Documentation')),
                esc_attr($this->plugin_data['Name']),
                __('Documentation')
            );

            $plugin_meta[] = sprintf(
                '<a href="%s" class="open-plugin-tickets" aria-label="%s" data-title="%s" target="_blank">%s</a>',
                esc_url('https://api.curie.ro/tickets'),
                esc_attr(__('Help')),
                esc_attr($this->plugin_data['Name']),
                __('Help')
            );

            foreach ($plugin_meta as $existing_link) {
                if (strpos($existing_link, 'tab=plugin-information') === false) {
                    return $plugin_meta;
                }
            }

            $plugin_meta[] = sprintf(
                '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
                esc_url(curiero_build_url('plugin-install.php?tab=plugin-information&plugin=curiero-plugin&TB_iframe=true')),
                esc_attr(sprintf(__('More information about %s'), $this->plugin_data['Name'])),
                esc_attr($this->plugin_data['Name']),
                __('View details')
            );
        }

        return $plugin_meta;
    }

    /**
     * Add the plugin settings link.
     *
     * @param array $links
     * @return array
     */
    public function action_links(array $links): array
    {
        $plugin_links = [
            '<a href="' . curiero_build_url('admin.php?page=curiero-menu-content') . '">' . __('Settings') . '</a>',
        ];

        return array_merge($plugin_links, $links);
    }

    /**
     * Register the plugin information during the plugin API call.
     *
     * @param mixed $res
     * @param string $action
     * @param object $args
     * @return mixed
     */
    public function handle_register_api($res, string $action, object $args)
    {
        // Do nothing if this is not about getting plugin information
        if ($action !== 'plugin_information') {
            return false;
        }

        // Do nothing if it is not our plugin
        if ('curiero-plugin' !== $args->slug) {
            return $res;
        }

        $remote = $this->check_update();
        if (!is_wp_error($remote)) {
            $remote = json_decode($remote['body']);

            if (empty($remote)) {
                return $res;
            }

            return $this->make_res($remote, true);
        }

        return false;
    }

    /**
     * Set the plugin update data during the transient update check.
     *
     * @param mixed $transient
     * @return mixed
     */
    public function handle_site_transient_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $remote = $this->check_update();
        if (!empty($remote) && !is_wp_error($remote)) {
            $remote = json_decode($remote['body']);

            if (empty($remote)) {
                return $transient;
            }

            $res = $this->make_res($remote, false);

            if (
                version_compare($this->plugin_data['Version'], $remote->version, '<')
                && version_compare($remote->requires, get_bloginfo('version'), '<')
                && version_compare($remote->requires_php, PHP_VERSION, '<=')
            ) {
                $transient->response[$res->plugin] = $res;
            } else {
                $transient->no_update[$res->plugin] = $res;
            }
        }

        return $transient;
    }

    /**
     * Clear CurieRO transients after plugin update.
     *
     * @param object $upgrader_object
     * @param array $options
     * @return void
     */
    public function handle_update_process_complete(object $upgrader_object, array $options): void
    {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && in_array($this->basename, $options['plugins'])) {
                CurieRO_Plugin_States::remove_transients();
            }
        }
    }

    /**
     * Clear the CurieRO update plugin transient if the user
     * forces a check.
     *
     * @return void
     */
    public function remove_curiero_update_plugin_transient(): void
    {
        if (!empty($_GET['force-check'])) {
            delete_transient('curiero_update_plugin');
        }
    }

    /**
     * Check for plugin updates against the CurieRO API.
     * If the server is unreachable, serve stale with current info.
     *
     * @return array|WP_Error
     **/
    protected function check_update()
    {
        if (false === $remote = get_transient('curiero_update_plugin')) {
            $remote = curiero_make_request(
                CURIERO_API_VERSION_JSON,
                'GET',
                [],
                ['Accept' => 'application/json'],
                3
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && $remote['response']['code'] === 200 && !empty($remote['body'])) {
                set_transient('curiero_update_plugin', $remote, HOUR_IN_SECONDS);
            } else {
                $remote = [
                    'response' => [
                        'code' => 200,
                        'message' => 'OK',
                    ],
                    'body' => json_encode([
                        'name' => $this->plugin_data['Name'],
                        'slug' => 'curiero-plugin',
                        'version' => $this->plugin_data['Version'],
                        'download_url' => CURIERO_API_URL . '/plugin/download/curiero-plugin',
                        'homepage' => $this->plugin_data['PluginURI'],
                        'author_homepage' => $this->plugin_data['AuthorURI'],
                        'requires' => '4.9.9', // Minimum WordPress version
                        'tested' => '6.5.1', // Latest tested WordPress version
                        'requires_php' => $this->plugin_data['RequiresPHP'], // Minimum PHP version
                        'author' => $this->plugin_data['Author'],
                        'section' => [
                            'description' => 'Plugin-ul CurieRO All-in-one - Generare AWB si Metode de livrare',
                            'changelog' => CURIERO_API_URL . 'plugin-changelog.txt',
                        ],
                    ]),
                ];

                set_transient('curiero_update_plugin', $remote, HOUR_IN_SECONDS);
            }
        }

        return $remote;
    }

    /**
     * Build the update response object.
     *
     * @param object $remote
     * @param bool $includeSections
     * @return object
     */
    protected function make_res(object $remote, bool $includeSections = false): object
    {
        $res = (object) [
            'name' => $remote->name,
            'slug' => $remote->slug,
            'author' => $remote->author,
            'author_profile' => $remote->author_homepage,
            'tested' => $remote->tested,
            'plugin' => $this->basename,
            'new_version' => $remote->version,
            'version' => $remote->version,
            'package' => $remote->download_url,
            'requires' => $remote->requires,
            'requires_php' => $remote->requires_php,
            'download_link' => $remote->download_url,
            'trunk' => $remote->download_url,
            'icons' => [
                'default' => CURIERO_PLUGIN_URL . 'assets/images/icon-256x256.png',
            ],
            'banners' => [
                'high' => CURIERO_PLUGIN_URL . 'assets/images/banner-1544x500.png',
                'low' => CURIERO_PLUGIN_URL . 'assets/images/banner-772x250.png',
            ],
            'sections' => [],
        ];

        if ($includeSections) {
            $changelog_request = curiero_make_request($remote->section->changelog);
            if (!is_wp_error($changelog_request)) {
                $changelog_contents = wp_remote_retrieve_body($changelog_request);
            } else {
                $changelog_contents = file_get_contents(CURIERO_PLUGIN_PATH . '/changelog.txt');
            }

            $markdownParser = new CurieRO\cebe\markdown\GithubMarkdown();
            $markdownParser->html5 = true;

            $res->sections['description'] = $remote->section->description;
            $res->sections['changelog'] = $markdownParser->parse($changelog_contents);

            if (!empty($remote->section->screenshots)) {
                $res->sections['screenshots'] = $remote->section->screenshots;
            }
        }

        return $res;
    }
}

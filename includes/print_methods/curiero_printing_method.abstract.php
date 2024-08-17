<?php

/**
 * Abstract printing class common base.
 *
 * @property string $alias
 * @property string $public_name
 */
abstract class CurieRO_Printing_Method
{
    /**
     * @var string
     */
    protected $class_path = '';

    /**
     * CurieRO_Printing_Method constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->class_path = (new ReflectionClass($this))->getFileName();

        add_action('admin_menu', [$this, 'add_courier_settings_page']);
        add_action('admin_menu', [$this, 'add_courier_generate_awb_page']);
        add_action('admin_init', [$this, 'add_courier_order_table_actions']);

        add_action('add_meta_boxes', [$this, 'add_courier_order_meta_box']);
        add_action('admin_notices', [$this, 'add_courier_account_status_notices'], 10);

        add_filter("option_page_capability_{$this::$alias}_settings", 'curiero_manage_options_capability');
    }

    /**
     * Add courier settings page.
     *
     * @return void
     */
    public function add_courier_settings_page(): void
    {
        add_submenu_page(
            'curiero-menu-content',
            "{$this::$public_name} - AWB",
            "{$this::$public_name} - AWB",
            curiero_manage_options_capability(),
            "{$this::$alias}_settings",
            [$this, 'courier_settings_page_template']
        );
    }

    /**
     * Render courier settings page.
     *
     * @return void
     */
    public function courier_settings_page_template(): void
    {
        wc_get_template(
            'templates/settings_page.php',
            [
                'courier_name' => $this::$public_name,
                'settings_page' => "{$this::$alias}_settings",
            ],
            '',
            plugin_dir_path($this->class_path),
        );
    }

    /**
     * Add courier generate AWB page.
     *
     * @return void
     */
    public function add_courier_generate_awb_page(): void
    {
        add_submenu_page(
            '__hidden_submenu_page',
            "Genereaza AWB {$this::$public_name}",
            "Genereaza AWB {$this::$public_name}",
            curiero_manage_options_capability(),
            "{$this::$alias}_generate_awb",
            [$this, 'courier_generate_awb_page_template'],
        );
    }

    /**
     * Render courier generate AWB page.
     *
     * @return void
     */
    public function courier_generate_awb_page_template(): void
    {
        $order_id = $_REQUEST['order_id'];
        $awb_details = static::getAwbDetails($order_id);

        wc_get_template(
            'templates/generate_awb_page.php',
            [
                'courier_name' => $this::$public_name,
                'order_id' => $order_id,
                'awb_details' => $awb_details,
            ],
            '',
            plugin_dir_path($this->class_path),
        );
    }

    /**
     * Add courier order meta box.
     *
     * @return void
     */
    public function add_courier_order_meta_box(): void
    {
        add_meta_box(
            "curiero_{$this::$alias}_metabox",
            "{$this::$public_name} - AWB",
            [$this, 'meta_box_callback'],
            curiero_get_shop_order_screen_id(),
            'side',
            'core'
        );
    }

    /**
     * Add courier order table actions.
     *
     * @return void
     */
    public function add_courier_order_table_actions(): void
    {
        $screen_id = curiero_get_shop_order_screen_id();

        if (CurieRO()->woocommerce_hpos_enabled) {
            add_filter("manage_{$screen_id}_columns", [$this, 'add_custom_columns_to_orders_table']);
            add_action("manage_{$screen_id}_custom_column", [$this, 'get_custom_columns_values'], 6, 2);
            add_filter("bulk_actions-{$screen_id}", [$this, 'add_bulk_generate_option'], 20, 1);
        } else {
            add_filter("manage_edit-{$screen_id}_columns", [$this, 'add_custom_columns_to_orders_table']);
            add_action("manage_{$screen_id}_posts_custom_column", [$this, 'get_custom_columns_values'], 6, 2);
            add_filter("bulk_actions-edit-{$screen_id}", [$this, 'add_bulk_generate_option'], 20, 1);
        }
    }

    /**
     * Add courier order table actions.
     *
     * @param array $bulk_options
     * @return array
     */
    public function add_bulk_generate_option(array $bulk_options = []): array
    {
        return array_merge(
            $bulk_options,
            ["generateAWB_{$this::$public_name}" => "Genereaza AWB {$this::$public_name}"]
        );
    }

    /**
     * Add courier order table columns.
     *
     * @param array $columns
     * @return array
     */
    public function add_custom_columns_to_orders_table(array $columns): array
    {
        return array_merge(
            $columns,
            ["{$this::$alias}_AWB" => $this::$public_name]
        );
    }

    /**
     * Add courier account status notices.
     *
     * @return void
     */
    public function add_courier_account_status_notices(): void
    {
        $post_type = get_query_var('post_type');

        if (($status_message = get_transient("{$this::$alias}_account_status")) && $post_type === 'shop_order') {
            ?>
            <div class="notice notice-warning">
                <p><?php _e($status_message, 'curiero-plugin'); ?></p>
            </div>
        <?php
        }

        if ($error_message = get_transient("{$this::$alias}_error_msg")) {
            ?>
            <div class="notice notice-warning">
                <p><?php _e($error_message, 'curiero-plugin'); ?></p>
            </div>
        <?php
            delete_transient("{$this::$alias}_error_msg");
        }
    }

    /**
     * Get AWB details.
     *
     * @param int $order_id
     * @return array
     */
    abstract public static function getAwbDetails(int $order_id): array;

    /**
     * Generate AWB.
     *
     * @param int $order_id
     * @param bool $bypass
     * @return void
     */
    abstract public static function generate_awb(int $order_id, bool $bypass = false): void;

    /**
     * Send AWB email.
     *
     * @param int $order_id
     * @param string $awb
     * @param array $awb_details
     * @return void
     */
    abstract public static function send_mails(int $order_id, string $awb, array $awb_details): void;

    /**
     * Auto-generate AWB.
     *
     * @param int $order_id
     * @return void
     */
    abstract public static function autogenerate_awb(int $order_id): void;

    /**
     * Register courier settings.
     *
     * @return void
     */
    abstract public function add_register_setting(): void;

    /**
     * Metabox callback.
     *
     * @param mixed $post
     * @return void
     */
    abstract public function meta_box_callback($post): void;

    /**
     * Get custom columns values.
     *
     * @param string $column
     * @param mixed $post
     * @return void
     */
    abstract public function get_custom_columns_values(string $column, $post): void;

    /**
     * Add courier account status notice.
     *
     * @param WC_Abstract_Order $order
     * @return void
     */
    abstract public function add_awb_notice(WC_Abstract_Order $order): void;

    /**
     * Register courier settings.
     *
     * @return void
     */
    abstract public function register_as_action(): void;

    /**
     * Update AWB status.
     *
     * @return void
     */
    abstract public function update_awb_status(): void;

    /**
     * Update AWB status chunk.
     *
     * @param array $order_ids
     * @return void
     */
    abstract public function update_awb_status_chunk(array $order_ids): void;
}

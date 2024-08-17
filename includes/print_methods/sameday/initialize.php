<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Sameday extends CurieRO_Printing_Method
{
    public static $alias = 'sameday';

    public static $public_name = 'Sameday';

    public static $awb_field = 'awb_sameday';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'handle_option_changes']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_action('curiero_fetch_sameday_easybox', [$this, 'fetch_sameday_easybox']);

        add_action('curiero_sameday_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_sameday_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_sameday_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('sameday_username', '');
        add_option('sameday_password', '');
        add_option('sameday_valid_auth', '0');
        add_option('sameday_package_type', '');
        add_option('sameday_pickup_point', '');
        add_option('sameday_ord_service_id', '7');
        add_option('sameday_locker_service_id', '15');
        add_option('sameday_declared_value', '0');
        add_option('sameday_observation', '');
        add_option('sameday_descriere_continut', 'nu');
        add_option('sameday_ord_additional_services', []);
        add_option('sameday_locker_additional_services', []);
        add_option('sameday_force_width', '');
        add_option('sameday_force_height', '');
        add_option('sameday_force_length', '');
        add_option('sameday_force_weight', '');
        add_option('sameday_trimite_mail', 'nu');
        add_option('sameday_page_type', 'A4');
        add_option('sameday_auto_generate_awb', 'nu');
        add_option('sameday_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'sameday_username');
        register_setting("{$this::$alias}_settings", 'sameday_password');
        register_setting("{$this::$alias}_settings", 'sameday_valid_auth');
        register_setting("{$this::$alias}_settings", 'sameday_pickup_point');
        register_setting("{$this::$alias}_settings", 'sameday_package_type');
        register_setting("{$this::$alias}_settings", 'sameday_ord_service_id');
        register_setting("{$this::$alias}_settings", 'sameday_locker_service_id');
        register_setting("{$this::$alias}_settings", 'sameday_declared_value');
        register_setting("{$this::$alias}_settings", 'sameday_ord_additional_services');
        register_setting("{$this::$alias}_settings", 'sameday_locker_additional_services');
        register_setting("{$this::$alias}_settings", 'sameday_force_width');
        register_setting("{$this::$alias}_settings", 'sameday_force_height');
        register_setting("{$this::$alias}_settings", 'sameday_force_length');
        register_setting("{$this::$alias}_settings", 'sameday_force_weight');
        register_setting("{$this::$alias}_settings", 'sameday_observation');
        register_setting("{$this::$alias}_settings", 'sameday_descriere_continut');
        register_setting("{$this::$alias}_settings", 'sameday_trimite_mail');
        register_setting("{$this::$alias}_settings", 'sameday_page_type');
        register_setting("{$this::$alias}_settings", 'sameday_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'sameday_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://sameday.ro/#awb=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('sameday', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('sameday', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
        } else {
            echo '<p><a href="' . curiero_build_url('admin.php', ['page' => "{$this::$alias}_generate_awb", 'order_id' => $order->get_id()]) . '" class="button button-primary" style="width: 100%; text-align: center;"><i class="dashicons dashicons-edit-page" style="vertical-align: middle; font-size: 16px;"></i> Genereaza AWB </a></p>';
        }
    }

    public function get_custom_columns_values(string $column, $post): void
    {
        if ($column !== "{$this::$alias}_AWB") {
            return;
        }

        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);
        $status = $order->get_meta('awb_sameday_status', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('sameday', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';
            echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '</div>';
            echo '</div>';
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Sameday', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/sameday.png" height="29"/></button></p>';
        }
    }

    public static function generate_awb(int $order_id, bool $bypass = false): void
    {
        if (get_query_var('post_type') !== 'shop_order' && !$bypass) {
            return;
        }

        $order = curiero_get_order($order_id);
        if ($order->meta_exists(static::$awb_field)) {
            return;
        }

        try {
            $trimite_mail = get_option('sameday_trimite_mail');

            $awb_info = self::getAwbDetails($order_id);
            $awb_info = apply_filters('curiero_awb_details_overwrite', $awb_info, self::$public_name, $order_id);

            $courier = CurieRO()->container->get(CurieroSamedayClass::class);
            $result = $courier->callMethod('generateAwb', $awb_info, 'POST');

            if ($result['status'] === 200) {
                $message = json_decode($result['message']);

                if (empty($message->error) && !empty($message->id)) {
                    $awb = $message->id;
                    if ($trimite_mail === 'da' && !empty($awb_info['email'])) {
                        static::send_mails($order_id, $awb, $awb_info);
                    }

                    $order->update_meta_data(static::$awb_field, $awb);
                    $order->update_meta_data('awb_sameday_status', 'Inregistrat');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('sameday_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('sameday_account_status');
                    }
                } else {
                    set_transient('sameday_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('sameday_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('sameday_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        global $wpdb;

        $sameday_username = get_option('sameday_username');
        if (empty($sameday_username)) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO Sameday AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $package_type = get_option('sameday_package_type');
        $pickup_point = get_option('sameday_pickup_point');
        $observation = get_option('sameday_observation');
        $descriere_continut = get_option('sameday_descriere_continut');
        $has_insurance = get_option('sameday_declared_value');
        $force_width = get_option('sameday_force_width');
        $force_height = get_option('sameday_force_height');
        $force_length = get_option('sameday_force_length');
        $force_weight = get_option('sameday_force_weight');

        $lockerLastMile = $order->get_meta('curiero_sameday_lockers', true);
        $service_id = empty($lockerLastMile) ? get_option('sameday_ord_service_id') : get_option('sameday_locker_service_id');
        $additional_services = empty($lockerLastMile) ? get_option('sameday_ord_additional_services', []) : get_option('sameday_locker_additional_services', []);

        [
            'weight' => $weight,
            'height' => $height,
            'width' => $width,
            'length' => $length,
            'contents' => $contents,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping
        ] = curiero_extract_order_items_details($order, $descriere_continut);

        if ($force_height) {
            $height = curiero_string_to_float($force_height);
        }

        if ($force_width) {
            $width = curiero_string_to_float($force_width);
        }

        if ($force_length) {
            $length = curiero_string_to_float($force_length);
        }

        if ($force_weight) {
            $weight = curiero_string_to_float($force_weight);
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO Sameday AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $city = $wpdb->get_var(
            $wpdb->prepare("SELECT sameday_locality_name FROM {$wpdb->prefix}curiero_localities WHERE county_initials='%s' AND locality_name='%s'", $order_details['state_short'], $order_details['city'])
        ) ?: $order_details['city'];

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        if ($order->get_payment_method() === 'cod') {
            $cod_value = $price_total;
        } else {
            $cod_value = 0;
        }

        $insurance = 0;
        if ($has_insurance == '1') {
            $insurance = $price_excl_shipping;
        }

        $awb_info = [
            'pickup_point' => $pickup_point,
            'package_type' => $package_type,
            'service_id' => $service_id,
            'observation' => $observation,
            'priceObservation' => $contents,
            'width' => $width,
            'height' => $height,
            'length' => $length,
            'weight' => $weight,
            'city' => $city,
            'state' => $order_details['state_long'],
            'address' => $address,
            'name' => $order_details['name'],
            'phone' => $order_details['phone'],
            'email' => $order_details['email'],
            'company' => $order_details['company'],
            'declared_value' => $insurance,
            'cod_value' => $cod_value,
            'reference' => null,
            'lockerFirstMile' => null,
            'lockerLastMile' => $lockerLastMile,
            'serviceTaxIds' => $additional_services,
        ];

        $awb_info = apply_filters('curiero_awb_details', $awb_info, static::$public_name, $order);
        $awb_info = array_map('curiero_remove_accents', $awb_info);

        return $awb_info;
    }

    public static function send_mails(int $order_id, string $awb, array $awb_info): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $awb_info['email'];
        $email_template = get_option('sameday_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_sameday_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_info['cod_value'],
            'sameday_easybox' => $order->get_meta('curiero_sameday_locker_name', true),
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('sameday_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('sameday_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_sameday_email', $email_content, $data);

        do_action('curiero_before_send_email', static::$public_name, $order, $awb);

        try {
            if (!$wc_mail->send($receiver_email, $subiect_mail, $email_content)) {
                set_transient('email_sent_error', 'Nu am putut trimite email-ul catre ' . $receiver_email, 5);
            } else {
                set_transient('email_sent_success', 'Email-ul s-a trimis catre ' . $receiver_email, 5);
            }
        } catch (Exception $e) {
            set_transient('email_sent_error', 'Nu am putut trimite email-ul catre ' . $receiver_email, 5);
        }
    }

    public static function autogenerate_awb(int $order_id): void
    {
        if (get_option('sameday_auto_generate_awb') !== 'da') {
            return;
        }
        as_schedule_single_action(time(), 'curiero_sameday_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://sameday.ro/#awb=%1$s" target="_blank">Status comanda</a></p>', $awb);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_sameday_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_sameday_awb_update', [], 'curiero_printing_methods');
        }
        if (false === as_next_scheduled_action('curiero_fetch_sameday_easybox')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fetch_sameday_easybox', [], 'curiero_printing_methods');
        }
    }

    public function fetch_sameday_easybox(): void
    {
        delete_transient('curiero_sameday_lockers');
        CurieRO()->container->get(CurieroSamedayClass::class)->getLockers();
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_sameday_status';
        $meta_value = ['Colete livrate', 'Coletul a fost livrat cu succes.'];

        // Calculate the date range for the past 3 weeks
        $three_weeks_ago = strtotime(apply_filters('curiero_awb_status_sync_date_range', '-3 weeks'));

        // Query the orders
        $args = [
            'posts_per_page' => -1,
            'date_query' => [
                'after' => date('Y-m-d', $three_weeks_ago),
            ],
            'curiero_meta' => [
                'key' => $meta_key,
                'value' => $meta_value,
                'compare' => 'NOT IN',
            ],
            'return' => 'ids',
        ];

        $orders = wc_get_orders($args);
        foreach (array_chunk($orders, 50) as $chunk) {
            as_schedule_single_action(time(), 'curiero_sameday_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_sameday_for_update = $order->get_meta(static::$awb_field, true);
            if (empty($awb_sameday_for_update)) {
                continue;
            }

            $courier = CurieRO()->container->get(APISamedayClass::class);
            $awb_status = $courier->getLatestStatus($awb_sameday_for_update);

            if (!$awb_status) {
                continue;
            }
            if ($awb_status === 'failed') {
                continue;
            }

            $order->update_meta_data('awb_sameday_status', $awb_status);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $awb_status, get_option('sameday_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $awb_status);
        }
    }

    public function handle_option_changes(): void
    {
        $delete_transients = function (?string $new_val, ?string $old_val): ?string {
            $key_token = CurieRO\Sameday\SamedayClient::KEY_TOKEN;
            $key_expires_token = CurieRO\Sameday\SamedayClient::KEY_TOKEN_EXPIRES;

            if ($old_val !== $new_val) {
                delete_transient('curiero_sameday_pickup_points');
                delete_transient('curiero_sameday_services');

                delete_transient("curiero_sameday_persistent_{$key_token}");
                delete_transient("curiero_sameday_persistent_{$key_expires_token}");
            }

            return $new_val;
        };

        add_filter('pre_update_option_sameday_username', $delete_transients, 10, 2);
        add_filter('pre_update_option_sameday_password', $delete_transients, 10, 2);
    }
}

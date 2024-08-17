<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Innoship extends CurieRO_Printing_Method
{
    public static $alias = 'innoship';

    public static $public_name = 'Innoship';

    public static $awb_field = 'awb_innoship';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);
        add_action('admin_init', [$this, 'handle_option_changes']);

        add_action('curiero_innoship_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_innoship_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_innoship_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('innoship_api_key', '');
        add_option('innoship_courier_id', '');
        add_option('innoship_valid_auth', '0');
        add_option('innoship_package_type', '');
        add_option('innoship_service_id', '1');
        add_option('innoship_location_id', '');
        add_option('innoship_declared_value', '0');
        add_option('innoship_page_type', 'A6');
        add_option('innoship_envelope_no', '0');
        add_option('innoship_package_no', '1');
        add_option('innoship_palette_no', '0');
        add_option('innoship_package_type', 'carton');
        add_option('innoship_package_contents', '');
        add_option('innoship_money_delivery_method', 'bank');
        add_option('innoship_delivery_payer', 'sender');
        add_option('innoship_observation_type', 'nu');
        add_option('innoship_observation', '');
        add_option('innoship_open_on_arrival', 'nu');
        add_option('innoship_saturday_delivery', 'nu');
        add_option('innoship_default_weight', '');
        add_option('innoship_trimite_mail', 'nu');
        add_option('innoship_auto_generate_awb', 'nu');
        add_option('innoship_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'innoship_api_key');
        register_setting("{$this::$alias}_settings", 'innoship_courier_id');
        register_setting("{$this::$alias}_settings", 'innoship_valid_auth');
        register_setting("{$this::$alias}_settings", 'innoship_package_type');
        register_setting("{$this::$alias}_settings", 'innoship_service_id');
        register_setting("{$this::$alias}_settings", 'innoship_location_id');
        register_setting("{$this::$alias}_settings", 'innoship_declared_value');
        register_setting("{$this::$alias}_settings", 'innoship_page_type');
        register_setting("{$this::$alias}_settings", 'innoship_envelope_no');
        register_setting("{$this::$alias}_settings", 'innoship_package_no');
        register_setting("{$this::$alias}_settings", 'innoship_palette_no');
        register_setting("{$this::$alias}_settings", 'innoship_package_type');
        register_setting("{$this::$alias}_settings", 'innoship_package_contents');
        register_setting("{$this::$alias}_settings", 'innoship_money_delivery_method');
        register_setting("{$this::$alias}_settings", 'innoship_delivery_payer');
        register_setting("{$this::$alias}_settings", 'innoship_open_on_arrival');
        register_setting("{$this::$alias}_settings", 'innoship_saturday_delivery');
        register_setting("{$this::$alias}_settings", 'innoship_observation_type');
        register_setting("{$this::$alias}_settings", 'innoship_observation');
        register_setting("{$this::$alias}_settings", 'innoship_default_weight');
        register_setting("{$this::$alias}_settings", 'innoship_trimite_mail');
        register_setting("{$this::$alias}_settings", 'innoship_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'innoship_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = maybe_unserialize($order->get_meta(static::$awb_field, true));

        if (!empty($awb)) {
            echo '<p><input type="text" value="' . $awb['awb'] . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="' . $awb['tracking_url'] . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('innoship', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('innoship', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $awb = maybe_unserialize($order->get_meta(static::$awb_field, true));
        $status = maybe_unserialize($order->get_meta('awb_innoship_status', true));

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('innoship', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb['awb'] . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';
            echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status['status'] . '</div>';
            echo '</div>';
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Innoship', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/innoship.png" height="29"/></button></p>';
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
            $trimite_mail = get_option('Innoship_trimite_mail');

            $awb_info = self::getAwbDetails($order_id);
            $awb_info = apply_filters('curiero_awb_details_overwrite', $awb_info, self::$public_name, $order_id);

            $courier = CurieRO()->container->get(CurieroInnoshipClass::class);
            $result = $courier->callMethod('generateAwb', $awb_info, 'POST');

            if ($result['status'] === 200) {
                $message = json_decode($result['message']);

                if (empty($message->error)) {
                    $awb_response_info = [
                        'awb' => $message->courierShipmentId,
                        'courier_id' => $message->courier,
                        'tracking_url' => $message->trackPageUrl,
                    ];

                    $awb_status_info = [
                        'status' => 'New',
                        'is_final' => false,
                    ];

                    $awb_extended_info = array_merge($awb_info, $awb_response_info);

                    if ($trimite_mail === 'da' && !empty($awb_info['email'])) {
                        static::send_mails($order_id, $awb_response_info['awb'], $awb_extended_info);
                    }

                    $order->update_meta_data(static::$awb_field, maybe_serialize($awb_response_info));
                    $order->update_meta_data('awb_innoship_status', maybe_serialize($awb_status_info));
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb_response_info['awb'], $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('innoship_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('innoship_account_status');
                    }
                } else {
                    set_transient('innoship_error_msg', 'Eroare la generare AWB: ' . $message->error, MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('innoship_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('innoship_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        $innoship_api_key = get_option('innoship_api_key');
        if (empty($innoship_api_key)) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO Innoship AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $location_id = get_option('innoship_location_id');
        $service_id = get_option('innoship_service_id');
        $innoship_observation_type = get_option('innoship_observation_type');
        $has_insurance = get_option('innoship_declared_value');

        [
            'weight' => $weight,
            'contents' => $contents,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping
        ] = curiero_extract_order_items_details($order, $innoship_observation_type);

        $observation = $contents;
        if ($innoship_observation_type === 'custom') {
            $observation = get_option('innoship_observation', '');
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO Innoship AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
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

        if ($locker_id = $order->get_meta('curiero_innoship_locker', true)) {
            $service_id = 3;
        }

        $awb_info = [
            'envelope_no' => get_option('innoship_envelope_no', 0),
            'courier_id' => get_option('innoship_courier_id', '') ?: null,
            'package_no' => get_option('innoship_package_no', 0),
            'packaging' => get_option('innoship_package_type', ''),
            'palette_no' => get_option('innoship_palette_no', 0),
            'contents' => get_option('innoship_package_contents', ''),
            'service_id' => $service_id,
            'location_id' => $location_id,
            'order_id' => $order_id,
            'observation' => $observation,
            'weight' => $weight,
            'city' => $order_details['city'],
            'county' => $order_details['state_long'],
            'address' => $order_details['address_full'],
            'name' => $order_details['name'],
            'phone' => $order_details['phone'],
            'email' => $order_details['email'],
            'postcode' => $order_details['postcode'],
            'country' => $order_details['country_short'],
            'company' => $order_details['company'],
            'currency' => get_woocommerce_currency(),
            'declared_value' => $insurance,
            'cod_value' => $cod_value,
            'open_on_arrival' => get_option('innoship_open_on_arrival', 'nu') === 'da',
            'saturday_delivery' => get_option('innoship_saturday_delivery', 'nu') === 'da',
            'delivery_payer' => get_option('innoship_delivery_payer', 'sender'),
            'money_delivery_method' => get_option('innoship_money_delivery_method', 'bank'),
            'locker_id' => $locker_id,
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
        $email_template = get_option('innoship_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_innoship_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_info['cod_value'],
            'innoship_link_urmarire' => $awb_info['tracking_url'],
            'innoship_denumire_curier' => APIInnoshipClass::getCourierName($awb_info['courier_id']),
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('innoship_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('innoship_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_innoship_email', $email_content, $data);

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
        if (get_option('innoship_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_innoship_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = maybe_unserialize($order->get_meta(static::$awb_field, true));
        if (!empty($awb)) {
            printf('<p>Nota de transport (AWB) are numarul: %s si poate fi urmarita aici: <a href="%s" target="_blank">Status comanda</a></p>', $awb['awb'], $awb['tracking_url']);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_innoship_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_innoship_awb_update', [], 'curiero_printing_methods');
        }
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_innoship_status';
        $meta_value = 's:8:"is_final";b:0;';

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
                'compare' => 'LIKE',
            ],
            'return' => 'ids',
        ];

        $orders = wc_get_orders($args);
        foreach (array_chunk($orders, 50) as $chunk) {
            as_schedule_single_action(time(), 'curiero_innoship_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_for_update = maybe_unserialize($order->get_meta(static::$awb_field, true));
            if (empty($awb_for_update)) {
                continue;
            }

            $courier = CurieRO()->container->get(APIInnoshipClass::class);
            $awb_status = $courier->getParcelStatus($awb_for_update['awb'], $awb_for_update['courier_id']);
            if (empty($awb_status)) {
                continue;
            }

            $status_info = [
                'status' => $awb_status['clientStatusDescription'],
                'is_final' => $awb_status['isFinalStatus'],
            ];

            $order->update_meta_data('awb_innoship_status', maybe_serialize($status_info));
            $order->save_meta_data();

            if (
                $status_info['is_final']
                && in_array($status_info['status'], ['Delivered', 'Livrat'])
            ) {
                curiero_mark_order_complete($order_id, 'Livrat', get_option('innoship_auto_mark_complete', 'nu'));
                curiero_autogenerate_invoice($order_id, 'Livrat');
            }
        }
    }

    public function handle_option_changes(): void
    {
        $clear_transients = function (?string $new_val, ?string $old_val): ?string {
            if ($old_val != $new_val) {
                delete_transient('curiero_innoship_client_locations');
                delete_transient('curiero_innoship_client_couriers');
                delete_transient('curiero_innoship_locker_list');
            }

            return $new_val;
        };

        add_filter('pre_update_option_innoship_api_key', $clear_transients, 10, 2);
        add_filter('pre_update_option_innoship_location_id', $clear_transients, 10, 2);
    }
}

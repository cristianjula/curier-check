<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Memex extends CurieRO_Printing_Method
{
    public static $alias = 'memex';

    public static $public_name = 'PTT Express';

    public static $awb_field = 'awb_memex';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'set_awb_pickup_time']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_action('curiero_memex_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_memex_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_memex_awb_generate', [$this, 'generate_awb'], 10, 2);
        add_action('curiero_memex_call_pickup', [$this, 'memex_call_pickup']);
    }

    public function add_register_setting(): void
    {
        add_option('memex_username', '');
        add_option('memex_password', '');
        add_option('memex_valid_auth', '0');
        add_option('memex_parcel_content', 'nu');
        add_option('memex_service_id', '38');
        add_option('memex_name', '');
        add_option('memex_address', '');
        add_option('memex_city', '');
        add_option('memex_postcode', '');
        add_option('memex_countrycode', 'RO');
        add_option('memex_person', '');
        add_option('memex_contact', '');
        add_option('memex_email', '');
        add_option('memex_is_private_person', 'false');
        add_option('memex_insurance', 'Nu');
        add_option('memex_additional_services', []);

        add_option('memex_package_count', '1');
        add_option('memex_envelope_count', '0');
        add_option('memex_parcel_length', '');
        add_option('memex_parcel_height', '');
        add_option('memex_parcel_width', '');
        add_option('memex_parcel_weight', '');
        add_option('memex_envelope_length', '');
        add_option('memex_envelope_height', '');
        add_option('memex_envelope_width', '');
        add_option('memex_envelope_weight', '');
        add_option('memex_parcel_note', '');
        add_option('memex_trimite_mail', 'nu');
        add_option('memex_label_format', 'PDF');
        add_option('memex_auto_generate_awb', 'nu');
        add_option('memex_auto_mark_complete', 'nu');
        add_option('memex_call_pickup', '');
        add_option('memex_pickup_time', '');
        add_option('memex_max_pickup_time', '');

        register_setting("{$this::$alias}_settings", 'memex_username');
        register_setting("{$this::$alias}_settings", 'memex_password');
        register_setting("{$this::$alias}_settings", 'memex_valid_auth');
        register_setting("{$this::$alias}_settings", 'memex_parcel_content');
        register_setting("{$this::$alias}_settings", 'memex_service_id');
        register_setting("{$this::$alias}_settings", 'memex_name');
        register_setting("{$this::$alias}_settings", 'memex_address');
        register_setting("{$this::$alias}_settings", 'memex_city');
        register_setting("{$this::$alias}_settings", 'memex_postcode');
        register_setting("{$this::$alias}_settings", 'memex_countrycode');
        register_setting("{$this::$alias}_settings", 'memex_person');
        register_setting("{$this::$alias}_settings", 'memex_email');
        register_setting("{$this::$alias}_settings", 'memex_contact');
        register_setting("{$this::$alias}_settings", 'memex_is_private_person');
        register_setting("{$this::$alias}_settings", 'memex_insurance');
        register_setting("{$this::$alias}_settings", 'memex_additional_services');

        register_setting("{$this::$alias}_settings", 'memex_package_count');
        register_setting("{$this::$alias}_settings", 'memex_envelope_count');
        register_setting("{$this::$alias}_settings", 'memex_parcel_length');
        register_setting("{$this::$alias}_settings", 'memex_parcel_height');
        register_setting("{$this::$alias}_settings", 'memex_parcel_width');
        register_setting("{$this::$alias}_settings", 'memex_parcel_weight');
        register_setting("{$this::$alias}_settings", 'memex_envelope_length');
        register_setting("{$this::$alias}_settings", 'memex_envelope_height');
        register_setting("{$this::$alias}_settings", 'memex_envelope_width');
        register_setting("{$this::$alias}_settings", 'memex_envelope_weight');
        register_setting("{$this::$alias}_settings", 'memex_parcel_note');
        register_setting("{$this::$alias}_settings", 'memex_is_sat_delivery');
        register_setting("{$this::$alias}_settings", 'memex_is_fragile');
        register_setting("{$this::$alias}_settings", 'memex_trimite_mail');
        register_setting("{$this::$alias}_settings", 'memex_label_format');
        register_setting("{$this::$alias}_settings", 'memex_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'memex_auto_mark_complete');
        register_setting("{$this::$alias}_settings", 'memex_call_pickup');
        register_setting("{$this::$alias}_settings", 'memex_pickup_time');
        register_setting("{$this::$alias}_settings", 'memex_max_pickup_time');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://pttexpress.ro/awb-tracking/?awb=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('memex', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('memex', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_memex_status', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('memex', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';
            echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '</div>';
            echo '</div>';
        } else {
            if ((int) get_option('memex_package_count') + (int) get_option('memex_envelope_count') <= 1) {
                echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Memex', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/ptt_logo.png" height="29"/></button> </p>';
            } else {
                echo '<p><a class="button generateBtn tips" data-tip="' . __('Genereaza AWB Memex', 'curiero-plugin') . '" href="' . curiero_build_url("admin.php?page=generate-awb-memex&order_id={$order->get_id()}") . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/ptt_logo.png"  height="29" /></a> </p>';
            }
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
            $trimite_mail = get_option('memex_trimite_mail');

            $awb_info = self::getAwbDetails($order_id);
            $awb_info = apply_filters('curiero_awb_details_overwrite', $awb_info, self::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroMemexClass::class);
            $result = $courier->callMethod('generateAwb', $awb_info, 'POST');

            if ($result['status'] === 200) {
                $message = json_decode($result['message'], true);

                if (empty($message['error'])) {
                    $awb = $message['awb'];

                    if (empty($awb)) {
                        throw new Exception('Nu s-a putut genera AWB-ul.');
                    }

                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb, $awb_info);
                    }

                    $order->update_meta_data(static::$awb_field, $awb);
                    $order->update_meta_data('awb_memex_status', 'Inregistrat');
                    $order->update_meta_data('memex_parcels', json_encode($awb_info['shipmentRequest']['Parcels']));
                    $order->update_meta_data('memex_awb_service_id', get_option('memex_service_id'));
                    $order->update_meta_data('memex_awb_generated_date', date('Y-m-d'));
                    $order->update_meta_data('memex_ship_from', json_encode($awb_info['shipmentRequest']['ShipFrom']));
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');

                    $account_status = json_decode($account_status_response['message']);
                    if ($account_status->show_message) {
                        set_transient('memex_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('memex_account_status');
                    }
                } else {
                    set_transient('memex_error_msg', 'Eroare la generare AWB: ' . $message['error'], MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('memex_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('memex_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        if (empty(get_option('memex_username', ''))) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO Memex AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $memex_parcel_content = get_option('memex_parcel_content');
        $package_count = (int) get_option('memex_package_count');
        $envelope_count = (int) get_option('memex_envelope_count');
        $has_insurance = get_option('memex_insurance');

        $addition_services = get_option('memex_additional_services');

        $length_env = get_option('memex_envelope_length');
        $height_env = get_option('memex_envelope_height');
        $width_env = get_option('memex_envelope_width');
        $weight_env = get_option('memex_envelope_weight');

        [
            'length' => $length,
            'height' => $height,
            'width' => $width,
            'weight' => $weight,
            'contents' => $contents,
            'price_total' => $price_total
        ] = curiero_extract_order_items_details($order, $memex_parcel_content);

        if ($height == 0) {
            $height = get_option('memex_parcel_height') ?: 10;
        }

        if ($width == 0) {
            $width = get_option('memex_parcel_width') ?: 10;
        }

        if ($length == 0) {
            $length = get_option('memex_parcel_length') ?: 10;
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO PTT Express AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $colete_parcel = [];
        if (!empty($package_count)) {
            $colete_parcel[] = [
                'Parcel' => [
                    'Type' => 'Package',
                    'Weight' => (string) round($weight),
                    'D' => $length,
                    'W' => $height,
                    'S' => $width,
                    'IsNST' => 'true',
                ],
            ];
        }

        $plicuri_parcel = [];
        if (!empty($envelope_count)) {
            $plicuri_parcel[] = [
                'Parcel' => [
                    'Type' => 'Envelope',
                    'Weight' => (string) round($weight_env),
                    'D' => $length_env,
                    'W' => $height_env,
                    'S' => $width_env,
                    'IsNST' => 'true',
                ],
            ];
        }

        $parcels = array_merge_recursive($colete_parcel, $plicuri_parcel);

        if ($order->get_payment_method() === 'cod') {
            $cod_amount = $price_total;
        } else {
            $cod_amount = 0;
        }

        $insurance = 0;
        if ($has_insurance === 'Da') {
            $insurance = $price_total;
        }

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        $awb_info = [
            'shipmentRequest' => [
                'ServiceId' => get_option('memex_service_id'),
                'ShipFrom' => [
                    'PointId' => '',
                    'Name' => get_option('memex_name'),
                    'Address' => get_option('memex_address'),
                    'City' => get_option('memex_city'),
                    'PostCode' => get_option('memex_postcode'),
                    'CountryCode' => get_option('memex_countrycode'),
                    'Person' => get_option('memex_person'),
                    'Contact' => get_option('memex_contact'),
                    'Email' => get_option('memex_email'),
                    'IsPrivatePerson' => get_option('memex_is_private_person'),
                ],
                'ShipTo' => [
                    'PointId' => '',
                    'Name' => curiero_strip_special_chars($order_details['company']) ?: curiero_strip_special_chars($order_details['name']),
                    'Address' => curiero_strip_special_chars($address),
                    'City' => curiero_strip_special_chars($order_details['city']),
                    'PostCode' => curiero_strip_special_chars($order_details['postcode']),
                    'CountryCode' => 'RO',
                    'Person' => empty($order_details['company']) ? curiero_strip_special_chars($order_details['name']) : curiero_strip_special_chars($order_details['company']),
                    'Contact' => curiero_strip_special_chars($order_details['phone']),
                    'Email' => curiero_strip_special_chars($order_details['email']),
                    'IsPrivatePerson' => empty($order_details['company']) ? 'true' : 'false',
                ],
                'Parcels' => $parcels,
                'COD' => [
                    'Amount' => $cod_amount,
                ],
                'InsuranceAmount' => (string) $insurance,
                'MPK' => '',
                'ContentDescription' => $contents,
                'RebateCoupon' => '',
                'LabelFormat' => get_option('memex_label_format'),
            ],
            'Parcels' => (string) $package_count,
            'Envelopes' => (string) $envelope_count,

            'additional_services' => $addition_services,
        ];

        // daca ambele coduri postale se afla in lista localitatilor pentru serviciul loco standard (ServiceID=121), atunci acesta este selectat automat
        $localities = ['077106', '077040', '077085', '077041', '077086', '077145', '077191', '077160', '077042', '077190', '077010', '077096', '077095'];

        if (
            (in_array($awb_info['shipmentRequest']['ShipFrom']['PostCode'], $localities) || strtolower($awb_info['shipmentRequest']['ShipFrom']['City']) == 'bucuresti')
            && (in_array($awb_info['shipmentRequest']['ShipTo']['PostCode'], $localities) || strtolower($awb_info['shipmentRequest']['ShipTo']['City']) == 'bucuresti')
        ) {
            $awb_info['shipmentRequest']['ServiceId'] = '121';
        }

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

        $receiver_email = $awb_info['shipmentRequest']['ShipTo']['Email'];
        $email_template = get_option('memex_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_memex_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_info['shipmentRequest']['COD']['Amount'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('memex_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('memex_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_memex_email', $email_content, $data);

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
        if (get_option('memex_auto_generate_awb') !== 'da') {
            return;
        }
        as_schedule_single_action(time(), 'curiero_memex_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %s si poate fi urmarita aici: <a href="https://pttexpress.ro/awb-tracking/?awb=%s" target="_blank">Status comanda</a></p>', $awb);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_memex_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_memex_awb_update', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_memex_call_pickup')) {
            if (get_option('memex_call_pickup') === '1' && get_option('memex_pickup_time')) {
                $time = get_option('memex_pickup_time');
                date_default_timezone_set('Europe/Bucharest');
                $next_schedule = strtotime($time . ' -1 hour') <= time() ? strtotime("tomorrow {$time} -1 hour") : strtotime($time . ' -1 hour');
                as_schedule_recurring_action($next_schedule, DAY_IN_SECONDS, 'curiero_memex_call_pickup', [], 'curiero_printing_methods');
            }
        }
    }

    public function set_awb_pickup_time(): void
    {
        add_filter('pre_update_option_memex_call_pickup', function (?string $new_val, ?string $old_val) {
            if ($new_val !== $old_val && $new_val === '0') {
                as_unschedule_all_actions('curiero_memex_call_pickup');
            }

            return $new_val;
        }, 10, 2);

        add_filter('pre_update_option_memex_pickup_time', function (?string $new_val, ?string $old_val) {
            if (get_option('memex_call_pickup') === '0') {
                return $new_val;
            }

            if ($old_val !== $new_val) {
                date_default_timezone_set('Europe/Bucharest');
                $next_schedule = strtotime($new_val . ' -1 hour') <= time() ? strtotime("tomorrow {$new_val} -1 hour") : strtotime($new_val . ' -1 hour');

                as_unschedule_all_actions('curiero_memex_call_pickup');
                as_schedule_recurring_action($next_schedule, DAY_IN_SECONDS, 'curiero_memex_call_pickup', [], 'curiero_printing_methods');
            }

            return $new_val;
        }, 10, 2);
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_memex_status';
        $meta_value = 'Livrat';

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
                'compare' => '!=',
            ],
            'return' => 'ids',
        ];

        $orders = wc_get_orders($args);
        foreach (array_chunk($orders, 50) as $chunk) {
            as_schedule_single_action(time(), 'curiero_memex_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_memex_for_update = $order->get_meta(static::$awb_field, true);
            if (!empty($awb_memex_for_update)) {
                $json_parameters = [
                    'packageNo' => $awb_memex_for_update,
                ];

                $courier = CurieRO()->container->get(CurieroMemexClass::class);
                $response = $courier->callMethod('trackParcel', $json_parameters, 'POST');

                if (empty($response['message'])) {
                    continue;
                }

                $response = json_decode($response['message']);
                if (empty($response->status)) {
                    continue;
                }

                $awb_status = $response->status;
                if ($awb_status !== 'failed') {
                    $order->update_meta_data('awb_memex_status', $awb_status);
                    $order->save_meta_data();

                    curiero_mark_order_complete($order_id, $awb_status, get_option('memex_auto_mark_complete', 'nu'));
                    curiero_autogenerate_invoice($order_id, $awb_status);
                }
            }
        }
    }

    public function memex_call_pickup(): void
    {
        $awbs = $parcels = $orders = $pickup_location = [
            '38' => [],
            '121' => [],
        ];

        $orders = wc_get_orders([
            'posts_per_page' => '-1',
            'curiero_meta' => [
                'relation' => 'AND', // AND relation by default
                [
                    'key' => 'awb_memex_status',
                    'value' => 'Inregistrat',
                    'compare' => '=',
                ],
                [
                    'key' => 'memex_awb_generated_date',
                    'value' => [date('Y-m-d', strtotime('-5 day')), date('Y-m-d', strtotime('-1 day'))], // awb urile generate cu 1-3 zile in urma
                    'compare' => 'BETWEEN',
                    'type' => 'DATE',
                ],
            ],
        ]);

        if (empty($orders)) {
            return;
        }

        foreach ($orders as $order) {
            $order_service_id = $order->get_meta('memex_awb_service_id', true);

            if ($order_service_id === '38') {
                $awbs['38'][] = $order->get_meta(static::$awb_field, true);
                $parcels['38'][] = json_decode($order->get_meta('memex_parcels', true), true);
                $orders['38'][] = $order;

                if (empty($pickup_location['38'])) {
                    $pickup_location['38'] = json_decode($order->get_meta('memex_ship_from', true), true);
                }
            }

            if ($order_service_id === '121') {
                $awbs['121'][] = $order->get_meta(static::$awb_field, true);
                $parcels['121'][] = json_decode($order->get_meta('memex_parcels', true), true);
                $orders['121'][] = $order;

                if (empty($pickup_location['121'])) {
                    $pickup_location['121'] = json_decode($order->get_meta('memex_ship_from', true), true);
                }
            }
        }

        foreach (array_keys($awbs) as $service_id) {
            if (empty($awbs[$service_id])) {
                continue;
            }

            $this->memex_call_pickup_request($pickup_location[$service_id], $awbs[$service_id], $parcels[$service_id], $orders[$service_id]);
        }
    }

    private function memex_call_pickup_request(array $pickup_location, array $awbs, array $parcels, array $orders): void
    {
        $courier = CurieRO()->container->get(CurieroMemexClass::class);
        $today = date('Y-m-d');

        $response = $courier->callMethod('callPickup', [
            'callPickupRequest' => [
                'PickupLocation' => $pickup_location,
                'ReadyDate' => date("Y-m-d\TH:i:s", strtotime($today . ' ' . get_option('memex_pickup_time'))),
                'MaxPickupDate' => date("Y-m-d\TH:i:s", strtotime($today . ' ' . get_option('memex_max_pickup_time'))),
                'PackageNo' => $awbs,
                'Parcels' => $parcels,
            ],
        ]);

        if (empty($response['message'])) {
            return;
        }

        $response = json_decode($response['message'], true);
        if (!$response['success']) {
            return;
        }

        $pickupNo = $response['pickupNo']['string'] ?? null;
        if (empty($pickupNo)) {
            return;
        }

        foreach ($orders as $order) {
            $order->update_meta_data('memex_pickup_no', $pickupNo);
            $order->update_meta_data('memex_pickup_date', $today);
            $order->delete_meta_data('memex_parcels');
            $order->delete_meta_data('memex_awb_service_id');
            $order->delete_meta_data('memex_awb_generated_date');
            $order->delete_meta_data('memex_ship_from');
            $order->save_meta_data();
        }
    }
}

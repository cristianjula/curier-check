<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_DPD extends CurieRO_Printing_Method
{
    public static $alias = 'dpd';

    public static $public_name = 'DPD';

    public static $awb_field = 'awb_dpd';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);
        add_action('admin_init', [$this, 'handle_option_changes']);

        add_action('curiero_fetch_dpd_box', [$this, 'update_dpd_box_list']);
        add_action('curiero_dpd_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_dpd_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_dpd_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('dpd_username', '');
        add_option('dpd_password', '');
        add_option('dpd_content_type', '');
        add_option('dpd_sender_id', '');
        add_option('dpd_service_id', '2505');
        add_option('dpd_international_service_id', '2212');
        add_option('dpd_parcel_count', '1');
        add_option('dpd_is_sat_delivery', '0');
        add_option('dpd_is_fragile', '0');
        add_option('dpd_parcel_note', '');
        add_option('dpd_trimite_mail', 'nu');
        add_option('dpd_courier_service_payer', 'SENDER');
        add_option('dpd_courier_package_payer', 'SENDER');
        add_option('dpd_page_type', 'A4');
        add_option('dpd_auto_generate_awb', 'nu');
        add_option('dpd_auto_mark_complete', 'nu');
        add_option('dpd_force_weight', '');
        add_option('dpd_obpd_option', '');
        add_option('dpd_obpd_return_service_id', '');
        add_option('dpd_obpd_return_payer', '');
        add_option('dpd_parcel_note_priority', 'admin');
        add_option('dpd_declared_value', '0');

        register_setting("{$this::$alias}_settings", 'dpd_username');
        register_setting("{$this::$alias}_settings", 'dpd_password');
        register_setting("{$this::$alias}_settings", 'dpd_content_type');
        register_setting("{$this::$alias}_settings", 'dpd_sender_id');
        register_setting("{$this::$alias}_settings", 'dpd_service_id');
        register_setting("{$this::$alias}_settings", 'dpd_international_service_id');
        register_setting("{$this::$alias}_settings", 'dpd_parcel_count');
        register_setting("{$this::$alias}_settings", 'dpd_parcel_note');
        register_setting("{$this::$alias}_settings", 'dpd_is_sat_delivery');
        register_setting("{$this::$alias}_settings", 'dpd_is_fragile');
        register_setting("{$this::$alias}_settings", 'dpd_trimite_mail');
        register_setting("{$this::$alias}_settings", 'dpd_page_type');
        register_setting("{$this::$alias}_settings", 'dpd_courier_service_payer');
        register_setting("{$this::$alias}_settings", 'dpd_courier_package_payer');
        register_setting("{$this::$alias}_settings", 'dpd_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'dpd_auto_mark_complete');
        register_setting("{$this::$alias}_settings", 'dpd_force_weight');
        register_setting("{$this::$alias}_settings", 'dpd_obpd_option');
        register_setting("{$this::$alias}_settings", 'dpd_obpd_return_service_id');
        register_setting("{$this::$alias}_settings", 'dpd_obpd_return_payer');
        register_setting("{$this::$alias}_settings", 'dpd_parcel_note_priority');
        register_setting("{$this::$alias}_settings", 'dpd_declared_value');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://tracking.dpd.ro/?shipmentNumber=' . $awb . '&language=ro" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('dpd', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('dpd', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $awb = $order->get_meta(self::$awb_field, true);
        $status = $order->get_meta('awb_dpd_status', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('dpd', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';
            echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '</div>';
            echo '</div>';
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB DPD', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/dpd.svg" height="29"/></button></p>';
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        if (empty(get_option('dpd_username'))) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO DPD AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $force_weight = get_option('dpd_force_weight');
        $dpd_descriere_continut = get_option('dpd_content_type');
        $observatii = get_option('dpd_parcel_note');
        $has_insurance = get_option('dpd_declared_value');

        [
            'weight' => $weight,
            'contents' => $contents,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping
        ] = curiero_extract_order_items_details($order, $dpd_descriere_continut);

        $contents = substr($contents, 0, 100);
        if (empty($contents)) {
            $contents = 'n/a';
        }

        if ($force_weight) {
            $weight = curiero_string_to_float($force_weight);
        }

        $order_details = curiero_extract_order_details($order);

        if ($order->get_payment_method() === 'cod' && get_option('dpd_international_service_id') != 2303) {
            $ramburs = $price_total;
        } else {
            $ramburs = 0;
        }

        $insurance = 0;
        if ($has_insurance == '1') {
            $insurance = $price_excl_shipping;
        }

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO DPD AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $obs = $observatii;
        if (get_option('dpd_parcel_note_priority') === 'client' && $order->get_customer_note() !== '') {
            $obs = $order->get_customer_note();
        }

        $address = $order_details['address_full'];
        $address_1 = $order_details['address_1'];
        $address_2 = $order_details['address_2'];

        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
            $address_1 = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
            $address_2 = '';
        }

        $awb_info = [
            'sender_id' => get_option('dpd_sender_id'),
            'service_id' => get_option('dpd_service_id'),
            'international_service_id' => get_option('dpd_international_service_id'),
            'language' => 'RO',
            'courier_service_payer' => get_option('dpd_courier_service_payer'),
            'package_payer' => get_option('dpd_courier_package_payer'),
            'third_party_client_id' => get_option('dpd_sender_id'),
            'package' => 'BOX',
            'contents' => $contents,
            'recipient_name' => $order_details['company'] ?: $order_details['name'],
            'recipient_contact' => !empty($order_details['company']) ? $order_details['name'] : '',
            'recipient_private_person' => empty($order_details['company']) ? 'y' : 'n',
            'recipient_phone' => $order_details['phone'],
            'recipient_address_country_name' => $order_details['country_short'],
            'recipient_address_state_id' => $order_details['country_long'] === 'Romania' ? $order_details['state_long'] : '',
            'recipient_address_site_name' => $order_details['city'],
            'recipient_address_postcode' => $order_details['postcode'],
            'recipient_address_note' => $address,
            'recipient_address_line1' => $address_1,
            'recipient_address_line2' => $address_2,
            'recipient_pickup_office_id' => $order->get_meta('curiero_dpd_box', true),
            'recipient_email' => $order_details['email'],
            'declared_value_amount' => $insurance,
            'saturday_delivery' => get_option('dpd_is_sat_delivery'),
            'declared_value_fragile' => get_option('dpd_is_fragile'),
            'cod_amount' => $ramburs,
            'cod_currency' => $order->get_currency(),
            'parcels_count' => get_option('dpd_parcel_count'),
            'total_weight' => $weight,
            'autoadjust_pickup_date' => 'y',
            'shipmentNote' => $obs,
            'ref1' => '',
            'obpd_option' => get_option('dpd_obpd_option'),
            'obpd_return_service_id' => get_option('dpd_obpd_option') ? get_option('dpd_obpd_return_service_id') : '',
            'obpd_return_payer' => get_option('dpd_obpd_option') ? get_option('dpd_obpd_return_payer') : '',
            'dropoff_office_id' => '',
            'recipient_address_country_id' => CurieRO()->container->get(APIDPDClass::class)->supported_countries[$order_details['country_short']]['numeric_iso']
        ];

        $awb_info = apply_filters('curiero_awb_details', $awb_info, static::$public_name, $order);
        $awb_info = array_map('curiero_remove_accents', $awb_info);

        return $awb_info;
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
            $awb_info = self::getAwbDetails($order_id);
            $trimite_mail = get_option('dpd_trimite_mail');

            if ($awb_info['recipient_address_country_name'] !== "RO") {
                $awb_info['service_id'] = get_option('dpd_international_service_id');
            }
            unset($awb_info['international_service_id']);

            $awb_info = apply_filters('curiero_awb_details_overwrite', $awb_info, self::$public_name, $order_id);

            $courier = CurieRO()->container->get(CurieroDPDClass::class);
            $result = $courier->callMethod('generateAwb', $awb_info, 'POST');

            if ($result['status'] === 200) {
                $message = json_decode($result['message']);

                if (empty($message->error)) {
                    $awb = $message->id;
                    if (empty($awb)) {
                        throw new Exception('A apărut o eroare la generarea AWB-ului. Vă rugăm să încercați din nou.');
                    }

                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb, $awb_info);
                    }

                    $order->update_meta_data(self::$awb_field, $awb);
                    $order->update_meta_data('awb_dpd_status', 'Inregistrat');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('dpd_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('dpd_account_status');
                    }
                } else {
                    set_transient('dpd_error_msg', 'Eroare la generare AWB: ' . $message->error->message, MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('dpd_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('dpd_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $awb_info): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $awb_info['recipient_email'];
        $email_template = get_option('dpd_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_dpd_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_info['cod_amount'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('dpd_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('dpd_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_dpd_email', $email_content, $data);

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
        if (get_option('dpd_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_dpd_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_dpd_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_dpd_awb_update', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_fetch_dpd_box')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fetch_dpd_box', [], 'curiero_printing_methods');
        }
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://tracking.dpd.ro/?shipmentNumber=%1$s&language=ro" target="_blank">Status AWB</a></p>', $awb);
        }
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_dpd_status';
        $meta_value = 'Expedierea ta a fost livrată cu success.';

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
            as_schedule_single_action(time(), 'curiero_dpd_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_dpd_for_update = $order->get_meta(self::$awb_field, true);
            if (empty($awb_dpd_for_update)) {
                continue;
            }

            $json_parameters = ['parcels' => $awb_dpd_for_update];

            $courier = CurieRO()->container->get(APIDPDClass::class);
            $awb_status = $courier->getLatestStatus($json_parameters);

            if (!$awb_status) {
                continue;
            }
            if ($awb_status === 'failed') {
                continue;
            }

            $order->update_meta_data('awb_dpd_status', $awb_status);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $awb_status, get_option('dpd_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $awb_status);
        }
    }

    public function update_dpd_box_list(): void
    {
        delete_transient('curiero_dpd_box_list');
        CurieRO()->container->get(CurieroDPDClass::class)->getDPDboxes();
    }

    public function handle_option_changes(): void
    {
        $clear_transients = function (?string $new_val, ?string $old_val): ?string {
            if ($old_val != $new_val) {
                delete_transient('curiero_dpd_service_list');
                delete_transient('curiero_dpd_sender_list');
            }

            return $new_val;
        };

        add_filter('pre_update_option_dpd_username', $clear_transients, 10, 2);
    }
}

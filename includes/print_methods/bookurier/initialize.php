<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Bookurier extends CurieRO_Printing_Method
{
    public static $alias = 'bookurier';

    public static $public_name = 'Bookurier';

    public static $awb_field = 'awb_bookurier';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_action('curiero_bookurier_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_bookurier_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_bookurier_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('bookurier_user', '');
        add_option('bookurier_password', '');
        add_option('bookurier_senderid', '');
        add_option('bookurier_trimite_mail', 'nu');
        add_option('bookurier_observatii', '');
        add_option('bookurier_pcount', '1');
        add_option('bookurier_services', '9');
        add_option('bookurier_insurance_val', '0');
        add_option('bookurier_auto_generate_awb', 'nu');
        add_option('bookurier_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'bookurier_user');
        register_setting("{$this::$alias}_settings", 'bookurier_password');
        register_setting("{$this::$alias}_settings", 'bookurier_senderid');
        register_setting("{$this::$alias}_settings", 'bookurier_observatii');
        register_setting("{$this::$alias}_settings", 'bookurier_trimite_mail');
        register_setting("{$this::$alias}_settings", 'bookurier_pcount');
        register_setting("{$this::$alias}_settings", 'bookurier_services');
        register_setting("{$this::$alias}_settings", 'bookurier_insurance_val');
        register_setting("{$this::$alias}_settings", 'bookurier_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'bookurier_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 100%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /></p>';
            echo '<p><a href="' . curiero_order_action_url('bookurier', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('bookurier', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_bookurier_status', true);

        $single_awb = explode(',', $awb);
        if (!empty($single_awb[0]) && is_array($single_awb)) {
            $printing_link = curiero_order_action_url('bookurier', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $single_awb[0] . '</a><br>';

            if (!empty($status)) {
                echo '<div class="curieroAWBNoticeWrapper">';
                echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '<br></div>';
                echo '</div>';
            }
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Bookurier', 'curiero-plugin') . '" data-courier="' . static::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/logo_bookurier.svg" height="29" /></button></p>';
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        $bookurier_user = get_option('bookurier_user');
        if (empty($bookurier_user)) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO Bookurier AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea AWB-ului.</h3>');
        }

        $client = get_option('bookurier_senderid');
        $service = get_option('bookurier_services');
        $pcount = get_option('bookurier_pcount');
        $notes = get_option('bookurier_observatii');
        $has_insurance = get_option('bookurier_insurance_val');

        [
            'weight' => $weight,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping
        ] = curiero_extract_order_items_details($order);

        if ($order->get_payment_method() === 'cod') {
            $ramburs = $price_total;
        } else {
            $ramburs = 0;
        }

        $insurance = 0;
        if ($has_insurance == '1') {
            $insurance = $price_excl_shipping;
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO Bookurier AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $consig_name = $order_details['company'] ?: $order_details['name'];

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        $awb_details = [
            'client' => $client,
            'unq' => $order_id,
            'recv' => $consig_name,
            'phone' => $order_details['phone'],
            'email' => $order_details['email'],
            'country' => $order_details['country_long'],
            'district' => $order_details['state_long'],
            'city' => $order_details['city'],
            'zip' => $order_details['postcode'],
            'street' => $address,
            'service' => $service,
            'packs' => $pcount,
            'exchange_pack' => '0',
            'weight' => $weight,
            'rbs_val' => $ramburs,
            'confirmation' => '0',
            'insurance_val' => $insurance,
            'notes' => $notes,
        ];

        $awb_details = apply_filters('curiero_awb_details', $awb_details, static::$public_name, $order);
        $awb_details = array_map('curiero_remove_accents', $awb_details);

        return $awb_details;
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
            $trimite_mail = get_option('bookurier_trimite_mail');
            $awb_details = static::getAwbDetails($order_id);
            $awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, static::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroBookurierClass::class);
            $response = $courier->callMethod('generateAwb', $awb_details, 'POST');

            if ($response['status'] === 200) {
                $mesage = json_decode($response['message'], true);

                if ($mesage['success']) {
                    $awb = $mesage['awb'];

                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb, $awb_details);
                    }

                    $order->update_meta_data(static::$awb_field, $awb);
                    $order->update_meta_data('awb_bookurier_status_id', '1');
                    $order->update_meta_data('awb_bookurier_status', 'Inregistrat');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('bookurier_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('bookurier_account_status');
                    }
                } else {
                    set_transient('bookurier_error_msg', 'Eroare la generare AWB.', MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('bookurier_error_msg', 'Eroare la generare AWB: ' . json_encode($response), MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('bookurier_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $awb_details): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $awb_details['email'];
        $email_template = get_option('bookurier_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_bookurier_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_details['rbs_val'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('bookurier_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('bookurier_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_bookurier_email', $email_content, $data);

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
        if (get_option('bookurier_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_bookurier_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://www.bookurier.ro/colete/AWB/track0.php" target="_blank">Status AWB</a></p>', $awb);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_bookurier_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_bookurier_awb_update', [], 'curiero_printing_methods');
        }
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_bookurier_status_id';
        $meta_value = '4';

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
            as_schedule_single_action(time(), 'curiero_bookurier_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_bookurier_for_update = $order->get_meta(static::$awb_field, true);

            if (empty($awb_bookurier_for_update)) {
                continue;
            }
            $awb_list = explode(',', $awb_bookurier_for_update);

            $courier = CurieRO()->container->get(APIBookurierClass::class);
            $response = $courier->getLatestStatus($awb_list[0]);

            if (!$response) {
                continue;
            }

            $order->update_meta_data('awb_bookurier_status', $response[0]);
            $order->update_meta_data('awb_bookurier_status_id', $response[1]);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $response[1], get_option('bookurier_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $response[1]);
        }
    }
}

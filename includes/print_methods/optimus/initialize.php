<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Optimus extends CurieRO_Printing_Method
{
    public static $alias = 'optimus';

    public static $public_name = 'Optimus';

    public static $awb_field = 'awb_optimus';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_action('curiero_optimus_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_optimus_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_optimus_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('optimus_username', '');
        add_option('optimus_key', '');
        add_option('optimus_valid_auth', 0);
        add_option('optimus_parcel_content', '');
        add_option('optimus_count', '1');
        add_option('optimus_parcel_weight', '');
        add_option('optimus_trimite_mail', 'nu');
        add_option('optimus_auto_generate_awb', 'nu');
        add_option('optimus_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'optimus_username');
        register_setting("{$this::$alias}_settings", 'optimus_key');
        register_setting("{$this::$alias}_settings", 'optimus_valid_auth');
        register_setting("{$this::$alias}_settings", 'optimus_parcel_content');
        register_setting("{$this::$alias}_settings", 'optimus_count');
        register_setting("{$this::$alias}_settings", 'optimus_parcel_weight');
        register_setting("{$this::$alias}_settings", 'optimus_trimite_mail');
        register_setting("{$this::$alias}_settings", 'optimus_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'optimus_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://optimuscourier.ro/search/?awb=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('optimus', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('optimus', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_optimus_status', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('optimus', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';
            echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '</div>';
            echo '</div>';
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Optimus', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/optimuslogo.png" height="29"/></button></p>';
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
            $trimite_mail = get_option('optimus_trimite_mail');

            $awb_info = self::getAwbDetails($order_id);
            $awb_info = apply_filters('curiero_awb_details_overwrite', $awb_info, self::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroOptimusClass::class);
            $result = $courier->callMethod('generateAwb', $awb_info, 'POST');

            if ($result['status'] === 200) {
                $message = json_decode($result['message'], true);

                if (empty($message->error)) {
                    $awb = $message['awb'];
                    $awb_id = $message['id'];

                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb, $awb_info);
                    }

                    $order->update_meta_data(static::$awb_field, $awb);
                    $order->update_meta_data('awb_optimus_status', 'Inregistrat');
                    $order->update_meta_data('awb_optimus_id', $awb_id);
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');

                    $account_status = json_decode($account_status_response['message']);
                    if ($account_status->show_message) {
                        set_transient('optimus_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('optimus_account_status');
                    }
                } else {
                    set_transient('optimus_error_msg', 'Eroare la generare AWB: ' . $message->error, MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('optimus_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('optimus_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        if (empty(get_option('optimus_username'))) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO Optimus AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        [
            'weight' => $weight,
            'content' => $content,
            'price_total' => $price_total,
        ] = curiero_extract_order_items_details($order);

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO Optimus AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        if ($order->get_payment_method() === 'cod') {
            $ramburs = $price_total;
        } else {
            $ramburs = 0;
        }

        $awb_info = [
            'destinatar_nume' => $order_details['company'] ?: $order_details['name'],
            'destinatar_adresa' => $address,
            'destinatar_localitate' => $order_details['city'],
            'destinatar_judet' => $order_details['state_long'],
            'destinatar_cod_postal' => $order_details['postcode'],
            'destinatar_contact' => $order_details['company'] ?: $order_details['name'],
            'destinatar_telefon' => $order_details['phone'],
            'colet_buc' => get_option('optimus_count'),
            'colet_greutate' => $weight,
            'data_colectare' => date('Y-m-d'),
            'ramburs_valoare' => $ramburs,
            'ref_factura' => 'Comanda ' . $order_details['number'],
            'colet_descriere' => $content ?? get_option('optimus_parcel_content'),
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

        $order = curiero_get_order($order_id);
        $receiver_email = $order->get_billing_email();
        $email_template = get_option('optimus_email_template');

        $data = apply_filters('curiero_overwrite_optimus_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order_id,
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_info['ramburs_valoare'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('optimus_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('optimus_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_optimus_email', $email_content, $data);

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
        if (get_option('optimus_auto_generate_awb') !== 'da') {
            return;
        }
        as_schedule_single_action(time(), 'curiero_optimus_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://optimuscourier.ro/search/?awb=%1$s" target="_blank">Status comanda</a></p>', $awb);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_optimus_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_optimus_awb_update', [], 'curiero_printing_methods');
        }
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_optimus_status';
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
            as_schedule_single_action(time(), 'curiero_optimus_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_optimus_for_update = $order->get_meta(static::$awb_field, true);
            if (empty($awb_optimus_for_update)) {
                continue;
            }

            $json_parameters = [
                'awb' => $awb_optimus_for_update,
            ];

            $courier = CurieRO()->container->get(CurieroOptimusClass::class);
            $response = $courier->callMethod('trackParcel', $json_parameters, 'POST');

            if (!$response) {
                continue;
            }
            if (!isset($response['message'])) {
                continue;
            }
            if (!(json_decode($response['message'])->status)) {
                continue;
            }

            $awb_status = json_decode($response['message'])->status;
            if ($awb_status === 'failed') {
                continue;
            }

            $order->update_meta_data('awb_optimus_status', $awb_status);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $awb_status, get_option('optimus_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $awb_status);
        }
    }
}

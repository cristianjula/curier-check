<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_GLS extends CurieRO_Printing_Method
{
    public static $alias = 'gls';

    public static $public_name = 'GLS Online';

    public static $awb_field = 'awb_GLS';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_filter('pre_update_option_GLS_other_senders', [$this, 'add_other_gls_senders'], 10, 2);
        add_action('wp_ajax_curiero_remove_other_gls_sender', [$this, 'remove_other_gls_sender']);

        add_action('curiero_gls_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_gls_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_gls_awb_generate', [$this, 'generate_awb'], 10, 2);

        include_once 'ziplookup.php';
    }

    public function add_register_setting(): void
    {
        add_option('GLS_user', '');
        add_option('GLS_password', '');
        add_option('GLS_senderid', '');
        add_option('GLS_sender_name', '');
        add_option('GLS_sender_address', '');
        add_option('GLS_sender_city', '');
        add_option('GLS_sender_zipcode', '');
        add_option('GLS_sender_contact', '');
        add_option('GLS_sender_phone', '');
        add_option('GLS_sender_email', '');
        add_option('GLS_trimite_mail', 'nu');
        add_option('GLS_show_content', 'nu');
        add_option('GLS_show_client_note', '0');
        add_option('GLS_show_order_id', '0');
        add_option('GLS_observatii', '');
        add_option('GLS_printertemplate', 'A4_2x2');
        add_option('GLS_pcount', '1');
        add_option('GLS_services', '');
        add_option('GLS_other_senders', '');
        add_option('GLS_auto_generate_awb', 'nu');
        add_option('GLS_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'GLS_user');
        register_setting("{$this::$alias}_settings", 'GLS_password');
        register_setting("{$this::$alias}_settings", 'GLS_senderid');
        register_setting("{$this::$alias}_settings", 'GLS_sender_name');
        register_setting("{$this::$alias}_settings", 'GLS_sender_address');
        register_setting("{$this::$alias}_settings", 'GLS_sender_city');
        register_setting("{$this::$alias}_settings", 'GLS_sender_zipcode');
        register_setting("{$this::$alias}_settings", 'GLS_sender_contact');
        register_setting("{$this::$alias}_settings", 'GLS_sender_phone');
        register_setting("{$this::$alias}_settings", 'GLS_sender_email');
        register_setting("{$this::$alias}_settings", 'GLS_observatii');
        register_setting("{$this::$alias}_settings", 'GLS_trimite_mail');
        register_setting("{$this::$alias}_settings", 'GLS_show_content');
        register_setting("{$this::$alias}_settings", 'GLS_show_client_note');
        register_setting("{$this::$alias}_settings", 'GLS_show_order_id');
        register_setting("{$this::$alias}_settings", 'GLS_printertemplate');
        register_setting("{$this::$alias}_settings", 'GLS_pcount');
        register_setting("{$this::$alias}_settings", 'GLS_services');
        register_setting("{$this::$alias}_settings", 'GLS_other_senders');
        register_setting("{$this::$alias}_settings", 'GLS_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'GLS_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://gls-group.eu/RO/ro/urmarire-colet?match=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('gls', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('gls', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_GLS_status', true);

        if (!empty($awb) && !is_array($awb)) {
            $printing_link = curiero_order_action_url('gls', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            if (!empty($status)) {
                echo '<div class="curieroAWBNoticeWrapper"><div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . ((strpos($status, '-') !== false) ? substr($status, 3) : $status) . '<br></div></div>';
            }
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB GLS', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/gls-button.png" height="29"/></button></p>';
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        if (empty(get_option('GLS_user', ''))) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO GLS AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $idExpeditor = get_option('GLS_senderid');
        $numeExpeditor = get_option('GLS_sender_name');
        $localitateExpeditor = get_option('GLS_sender_city');
        $adresaExpeditor = get_option('GLS_sender_address');
        $codPostalExpeditor = get_option('GLS_sender_zipcode');
        $telefonExpeditor = get_option('GLS_sender_phone');
        $emailExpeditor = get_option('GLS_sender_email');
        $persoanaDeContact = get_option('GLS_sender_contact');
        $observatii = get_option('GLS_observatii');
        $printertemplate = get_option('GLS_printertemplate');
        $show_contents = get_option('GLS_show_content');
        $show_client_note = get_option('GLS_show_client_note');
        $show_order_id = get_option('GLS_show_order_id');
        $services = get_option('GLS_services');
        $pcount = get_option('GLS_pcount');

        [
            'weight' => $weight,
            'contents' => $contents,
            'price_total' => $price_total
        ] = curiero_extract_order_items_details($order, $show_contents);

        if ($order->get_payment_method() === 'cod') {
            $ramburs = $price_total;
        } else {
            $ramburs = 0;
        }

        $client_notes = '';
        if ($show_client_note == '1' && $order->get_customer_note() != '') {
            $client_notes = 'Nota client: ' . $order->get_customer_note();
            $observatii = $observatii . ' ' . $client_notes;
        }

        if ($show_order_id == '1') {
            $observatii = $observatii . ' #' . $order->ID;
        }

        if (!empty($contents)) {
            $contents = 'Continut: ' . ltrim($contents, ', ');
        }

        if (!empty($observatii)) {
            $contents = $observatii . ' ' . $contents;
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO GLS AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $consig_name = $order_details['company'] ?: $order_details['name'];

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        $awb_details = [
            'senderid' => $idExpeditor,
            'sender_name' => $numeExpeditor,
            'sender_contact' => $persoanaDeContact,
            'sender_country' => WC()->countries->get_base_country(),
            'sender_city' => $localitateExpeditor,
            'sender_address' => $adresaExpeditor,
            'sender_zipcode' => $codPostalExpeditor,
            'sender_phone' => $telefonExpeditor,
            'sender_email' => $emailExpeditor,

            'consig_name' => $consig_name,
            'consig_county' => $order_details['state_long'],
            'consig_country' => $order_details['country_short'],
            'consig_contact' => $order_details['name'],
            'consig_city' => $order_details['city'],
            'consig_address' => $address,
            'consig_zipcode' => $order_details['postcode'],
            'consig_phone' => $order_details['phone'],
            'consig_email' => $order_details['email'],

            'pcount' => $pcount,
            'pickupdate' => date('Y-m-d'),
            'content' => $contents,
            'clientref' => '',
            'codamount' => $ramburs,
            'codref' => '',
            'services' => $services,
            'printertemplate' => $printertemplate,
            'printit' => true,
            'timestamp' => time(),
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
            $trimite_mail = get_option('GLS_trimite_mail');

            $awb_details = self::getAwbDetails($order_id);
            $awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, self::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroGLSClass::class);
            $response = $courier->callMethod('generateAwb', $awb_details, 'POST');

            if ($response['status'] === 200) {
                $mesage = json_decode($response['message'], true);
                $successfull = $mesage['successfull'];

                if ($successfull) {
                    $awb = $mesage['pcls'][0];

                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb, $awb_details);
                    }

                    $order->update_meta_data(static::$awb_field, $awb);
                    $order->update_meta_data('awb_GLS_all_pcls', $mesage['all_pcls']);
                    $order->update_meta_data('awb_GLS_status', 'Inregistrat');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('gls_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('gls_account_status');
                    }
                } else {
                    set_transient('gls_error_msg', 'Eroare la generare AWB: ' . $mesage['errdesc'], MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('gls_error_msg', 'Eroare la generare AWB: ' . $response['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('gls_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $awb_details): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () { return 'text/html'; });

        $receiver_email = $awb_details['consig_email'];
        $email_template = get_option('GLS_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_gls_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_details['codamount'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('GLS_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('GLS_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_GLS_email', $email_content, $data);

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

    public function add_other_gls_senders($new_senders, $other_senders): array
    {
        $other_senders = maybe_unserialize($other_senders ?: []);
        $new_senders = maybe_unserialize($new_senders ?: []);

        if (is_null($new_senders)) {
            return $other_senders;
        }

        if (isset($new_senders['new'])) {
            $other_senders[wp_rand()] = array_merge([
                'name' => '',
                'address' => '',
                'city' => '',
                'zipcode' => '',
                'phone' => '',
                'email' => '',
                'contact' => '',
            ], $new_senders['new']);

            unset($new_senders['new']);

            return $other_senders;
        }

        return $new_senders;
    }

    public function remove_other_gls_sender(): void
    {
        if (!current_user_can('curiero_can_manage_options')) {
            wp_send_json_error('Invalid security token sent or insufficient permissions.', 403);
        }

        $remove_key = $_POST['remove_GLS_other_sender'];
        $current_other_senders = maybe_unserialize(get_option('GLS_other_senders', ''));

        unset($current_other_senders[$remove_key]);
        update_option('GLS_other_senders', $current_other_senders);
    }

    public static function autogenerate_awb(int $order_id): void
    {
        if (get_option('GLS_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_gls_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://gls-group.eu/RO/ro/urmarire-colet?match=%1$s" target="_blank">Status comanda</a></p>', $awb);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_gls_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_gls_awb_update', [], 'curiero_printing_methods');
        }
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_GLS_status';
        $meta_value = '05-Livrat';

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
            as_schedule_single_action(time(), 'curiero_gls_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (!count($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_GLS_for_update = $order->get_meta(static::$awb_field, true);
            if (empty($awb_GLS_for_update)) {
                continue;
            }

            $courier = CurieRO()->container->get(APIGlsClass::class);
            $awb_status = $courier->getParcelStatus($awb_GLS_for_update);

            if (!$awb_status) {
                continue;
            }

            if ($awb_status === 'failed') {
                continue;
            }

            $order->update_meta_data('awb_GLS_status', $awb_status);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $awb_status, get_option('GLS_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $awb_status);
        }
    }
}

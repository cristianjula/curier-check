<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_MyGLS extends CurieRO_Printing_Method
{
    public static $alias = 'mygls';

    public static $public_name = 'MyGLS';

    public static $awb_field = 'awb_mygls';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_filter('pre_update_option_MyGLS_other_senders', [$this, 'add_other_mygls_senders'], 10, 2);
        add_action('wp_ajax_curiero_remove_other_mygls_sender', [$this, 'remove_other_mygls_sender']);

        add_action('curiero_fetch_mygls_box', [$this, 'fetch_mygls_box']);
        add_action('curiero_mygls_awb_cleanup', [$this, 'clean_awb_data']);

        add_action('curiero_mygls_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_mygls_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_mygls_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('MyGLS_user', '');
        add_option('MyGLS_password', '');
        add_option('MyGLS_clientnumber', '');
        add_option('MyGLS_sender_name', '');
        add_option('MyGLS_sender_address', '');
        add_option('MyGLS_sender_city', '');
        add_option('MyGLS_sender_zipcode', '');
        add_option('MyGLS_sender_contact', '');
        add_option('MyGLS_sender_phone', '');
        add_option('MyGLS_sender_email', '');
        add_option('MyGLS_trimite_mail', 'nu');
        add_option('MyGLS_show_content', 'nu');
        add_option('MyGLS_show_client_note', '0');
        add_option('MyGLS_show_order_id', '0');
        add_option('MyGLS_observatii', '');
        add_option('MyGLS_printertemplate', 'A4_2x2');
        add_option('MyGLS_pcount', '1');
        add_option('MyGLS_services', '');
        add_option('MyGLS_other_senders', '');
        add_option('MyGLS_auto_generate_awb', 'nu');
        add_option('MyGLS_auto_mark_complete', 'nu');
        add_option('MyGLS_auto_cleanup_awb', '90');

        register_setting("{$this::$alias}_settings", 'MyGLS_user');
        register_setting("{$this::$alias}_settings", 'MyGLS_password');
        register_setting("{$this::$alias}_settings", 'MyGLS_clientnumber');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_name');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_address');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_city');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_zipcode');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_contact');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_phone');
        register_setting("{$this::$alias}_settings", 'MyGLS_sender_email');
        register_setting("{$this::$alias}_settings", 'MyGLS_observatii');
        register_setting("{$this::$alias}_settings", 'MyGLS_trimite_mail');
        register_setting("{$this::$alias}_settings", 'MyGLS_show_content');
        register_setting("{$this::$alias}_settings", 'MyGLS_show_client_note');
        register_setting("{$this::$alias}_settings", 'MyGLS_show_order_id');
        register_setting("{$this::$alias}_settings", 'MyGLS_printertemplate');
        register_setting("{$this::$alias}_settings", 'MyGLS_pcount');
        register_setting("{$this::$alias}_settings", 'MyGLS_services');
        register_setting("{$this::$alias}_settings", 'MyGLS_other_senders');
        register_setting("{$this::$alias}_settings", 'MyGLS_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'MyGLS_auto_mark_complete');
        register_setting("{$this::$alias}_settings", 'MyGLS_auto_cleanup_awb');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb_nr = $order->get_meta('awb_mygls_parcelnumber', true);

        if ($awb_nr) {
            echo '<p><input type="text" value="' . $awb_nr . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://gls-group.eu/RO/ro/urmarire-colet?match=' . $awb_nr . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('mygls', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('mygls', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $awb_nr = $order->get_meta('awb_mygls_parcelnumber');
        $status = $order->get_meta('awb_mygls_status', true);

        if (!empty($awb_nr)) {
            $printing_link = curiero_order_action_url('mygls', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee;">' . $awb_nr . '</a><br>';

            if (!empty($status)) {
                echo '<div class="curieroAWBNoticeWrapper"><div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . ((strpos($status, '-') !== false) ? substr($status, 3) : $status) . '<br></div></div>';
            }
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB GLS', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/logo_mygls.svg" height="29"/></button></p>';
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        if (empty(get_option('MyGLS_user', ''))) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO GLS AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $idExpeditor = get_option('MyGLS_clientnumber');
        $numeExpeditor = get_option('MyGLS_sender_name');
        $localitateExpeditor = get_option('MyGLS_sender_city');
        $adresaExpeditor = get_option('MyGLS_sender_address');
        $codPostalExpeditor = get_option('MyGLS_sender_zipcode');
        $telefonExpeditor = get_option('MyGLS_sender_phone');
        $emailExpeditor = get_option('MyGLS_sender_email');
        $persoanaDeContact = get_option('MyGLS_sender_contact');
        $observatii = get_option('MyGLS_observatii');
        $printertemplate = get_option('MyGLS_printertemplate');
        $show_contents = get_option('MyGLS_show_content');
        $show_client_note = get_option('MyGLS_show_client_note');
        $show_order_id = get_option('MyGLS_show_order_id');
        $services = get_option('MyGLS_services');
        $pcount = get_option('MyGLS_pcount');
        $mygls_box_id = $order->get_meta('curiero_mygls_box', true);

        [
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
            $observatii = trim($observatii . ' ' . $client_notes);
        }

        if ($show_order_id == '1') {
            $observatii = trim($observatii . ' #' . $order->get_order_number());
        }

        if (!empty($contents)) {
            $contents = 'Continut: ' . $contents;
        }

        if (!empty($observatii)) {
            $contents = trim($observatii . ' ' . $contents);
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

            'clientNumber' => $idExpeditor,
            'myglsbox_id' => $mygls_box_id,
            'pcount' => $pcount,
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
            $trimite_mail = get_option('MyGLS_trimite_mail');

            $awb_details = self::getAwbDetails($order_id);
            $awb_details = apply_filters('curiero_awb_details_overwrite', $awb_details, self::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroMyGLSClass::class);
            $response = $courier->callMethod('generateAwb', $awb_details, 'POST');

            if ($response['status'] === 200) {
                $mesage = json_decode($response['message'], true);
                $successful = $mesage['success'];

                if ($successful) {
                    $awb_id = $mesage['parcelId'];
                    $awb_nr = $mesage['parcelNumber'];
                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $awb_nr, $awb_details);
                    }

                    $pdf_string = '';
                    foreach ($mesage['labels'] as $label) {
                        $pdf_string .= chr($label);
                    }

                    global $wpdb;
                    $wpdb->insert(
                        "{$wpdb->prefix}curiero_mygls_awb_data",
                        [
                            'order_id' => $order_id,
                            'awb_number' => $awb_nr,
                            'awb_data' => base64_encode(gzencode($pdf_string, 9)),
                            'created_at' => current_time('mysql'),
                        ]
                    );

                    $order->update_meta_data(static::$awb_field, $awb_id);
                    $order->update_meta_data('awb_mygls_parcelnumber', $awb_nr);
                    $order->update_meta_data('awb_mygls_status', 'Inregistrat');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $awb_id, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('mygls_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('mygls_account_status');
                    }
                } else {
                    set_transient('mygls_error_msg', 'Eroare la generare AWB: ' . $mesage['errdesc'], MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('mygls_error_msg', 'Eroare la generare AWB: ' . $response['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('mygls_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $awb_details): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $awb_details['consig_email'];
        $email_template = get_option('MyGLS_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_mygls_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awb_details['codamount'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('MyGLS_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('MyGLS_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_MyGLS_email', $email_content, $data);

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

    public function add_other_mygls_senders($new_senders, $other_senders): array
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

    public function remove_other_mygls_sender(): void
    {
        if (!current_user_can('curiero_can_manage_options')) {
            wp_send_json_error('Invalid security token sent or insufficient permissions.', 403);
        }

        $remove_key = $_POST['remove_MyGLS_other_sender'];
        $current_other_senders = maybe_unserialize(get_option('MyGLS_other_senders', ''));

        unset($current_other_senders[$remove_key]);
        update_option('MyGLS_other_senders', $current_other_senders);
    }

    public static function autogenerate_awb(int $order_id): void
    {
        if (get_option('MyGLS_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_mygls_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb_nr = $order->get_meta('awb_mygls_parcelnumber', true);
        if ($awb_nr) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://gls-group.eu/RO/ro/urmarire-colet?match=%1$s" target="_blank">Status comanda</a></p>', $awb_nr);
        }
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_mygls_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_mygls_awb_update', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_fetch_mygls_box')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fetch_mygls_box', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_mygls_awb_cleanup')) {
            as_schedule_recurring_action(time(), 24 * HOUR_IN_SECONDS, 'curiero_mygls_awb_cleanup', [], 'curiero_printing_methods');
        }
    }

    public function fetch_mygls_box(): void
    {
        delete_transient('curiero_mygls_locker_list');
        CurieRO()->container->get(CurieroMyGLSClass::class)->getLockers();
    }

    public function clean_awb_data(): void
    {
        global $wpdb;

        $auto_cleanup_period = get_option('MyGLS_auto_cleanup_awb');
        if ($auto_cleanup_period === 'nu' || !is_numeric($auto_cleanup_period)) {
            return;
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}curiero_mygls_awb_data WHERE created_at < NOW() - INTERVAL %d DAY",
                $auto_cleanup_period
            )
        );
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_mygls_status';
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
            as_schedule_single_action(time(), 'curiero_mygls_awb_update_chunk', [$chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (empty($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_nr = $order->get_meta('awb_mygls_parcelnumber');

            if (empty($awb_nr)) {
                continue;
            }

            $courier = CurieRO()->container->get(CurieroMyGLSClass::class);
            $statusRequest = $courier->callMethod('getStatusAwb', ['parcelNumber' => $awb_nr], 'POST');
            $awb_response = json_decode($statusRequest['message'], true);

            if (
                $statusRequest['status'] !== 200
                || $awb_response['success'] !== true
            ) {
                continue;
            }

            $last_status = $awb_response['status']['StatusDescription'] ?? '';
            $last_status = ucfirst($last_status);

            if (empty($last_status)) {
                continue;
            }

            $order->update_meta_data('awb_mygls_status', $last_status);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $last_status, get_option('MyGLS_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $last_status);
        }
    }
}

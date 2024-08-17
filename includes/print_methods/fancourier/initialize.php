<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Printing_Fan extends CurieRO_Printing_Method
{
    public static $alias = 'fancourier';

    public static $public_name = 'FanCourier';

    public static $awb_field = 'awb_fan';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'handle_option_changes']);
        add_action('admin_init', [$this, 'register_as_action']);

        add_action('curiero_fetch_fan_box', [$this, 'fetch_fan_box']);

        add_action('curiero_fan_courier_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_fan_courier_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_fan_courier_awb_transfers_chunk', [$this, 'update_awb_transfers_chunk']);
        add_action('curiero_fan_courier_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('fan_clientID', '');
        add_option('fan_user', '');
        add_option('fan_password', '');
        add_option('fan_service', 'Cont Colector');
        add_option('fan_valid_auth', '0');
        add_option('fan_nr_colete', '1');
        add_option('fan_nr_plicuri', '0');
        add_option('fan_observatii', '');
        add_option('fan_plata_transport', 'expeditor');
        add_option('fan_plata_ramburs', 'expeditor');
        add_option('fan_asigurare', 'nu');
        add_option('fan_deschidere', '');
        add_option('fan_sambata', '');
        add_option('fan_contact_exp', '');
        add_option('fan_descriere_continut', 'nu');
        add_option('fan_trimite_mail', 'nu');
        add_option('fan_personal_data', '');
        add_option('fan_epod_opod', 'nu');
        add_option('fan_page_type', '');
        add_option('fan_force_width', '');
        add_option('fan_force_height', '');
        add_option('fan_force_length', '');
        add_option('fan_force_weight', '');
        add_option('fan_auto_generate_awb', 'nu');
        add_option('fan_auto_mark_complete', 'nu');

        register_setting("{$this::$alias}_settings", 'fan_clientID');
        register_setting("{$this::$alias}_settings", 'fan_user');
        register_setting("{$this::$alias}_settings", 'fan_password');
        register_setting("{$this::$alias}_settings", 'fan_service');
        register_setting("{$this::$alias}_settings", 'fan_valid_auth');
        register_setting("{$this::$alias}_settings", 'fan_nr_colete');
        register_setting("{$this::$alias}_settings", 'fan_nr_plicuri');
        register_setting("{$this::$alias}_settings", 'fan_observatii');
        register_setting("{$this::$alias}_settings", 'fan_plata_transport');
        register_setting("{$this::$alias}_settings", 'fan_plata_ramburs');
        register_setting("{$this::$alias}_settings", 'fan_asigurare');
        register_setting("{$this::$alias}_settings", 'fan_deschidere');
        register_setting("{$this::$alias}_settings", 'fan_sambata');
        register_setting("{$this::$alias}_settings", 'fan_contact_exp');
        register_setting("{$this::$alias}_settings", 'fan_descriere_continut');
        register_setting("{$this::$alias}_settings", 'fan_trimite_mail');
        register_setting("{$this::$alias}_settings", 'fan_personal_data');
        register_setting("{$this::$alias}_settings", 'fan_epod_opod');
        register_setting("{$this::$alias}_settings", 'fan_page_type');
        register_setting("{$this::$alias}_settings", 'fan_force_width');
        register_setting("{$this::$alias}_settings", 'fan_force_height');
        register_setting("{$this::$alias}_settings", 'fan_force_length');
        register_setting("{$this::$alias}_settings", 'fan_force_weight');
        register_setting("{$this::$alias}_settings", 'fan_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'fan_auto_mark_complete');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://www.fancourier.ro/awb-tracking/?tracking=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('fancourier', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('fancourier', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_fan_status', true);
        $ordin_plata_ramburs = $order->get_meta('ordin_plata_ramburs', true);
        $ordin_plata_ramburs_value = $order->get_meta('ordin_plata_ramburs_value', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('fancourier', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee;">' . $awb . '</a>';
            echo '<div class="curieroAWBNoticeWrapper">';

            if (!empty($status)) {
                if ($status == 'Livrat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Expeditie in livrare') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons dashicons-migrate"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Avizat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-images-alt"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Adresa incompleta') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-image-rotate"></span>Status: ' . $status . '<br></div>';
                } else {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-info"></span>Status: ' . $status . '<br></div>';
                }

                if ($ordin_plata_ramburs) {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Ramburs: ' . $ordin_plata_ramburs . '<br></div>';
                }
                if ($ordin_plata_ramburs_value) {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Valoare ramburs: ' . $ordin_plata_ramburs_value . '<br></div>';
                }
            } else {
                echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-warning"></span>Status: Nescanat</div>';
            }

            echo '</div>';
        } else {
            echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB FanCourier', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/fancourier.png" height="29" /></button></p>';
        }
    }

    public static function getAwbDetails(int $order_id): array
    {
        global $wpdb;

        $order = curiero_get_order($order_id);
        $awb_already_generated = $order->meta_exists(static::$awb_field);
        if ($awb_already_generated || !$order) {
            wp_die('<h3>Eroare la generarea awb-ului.</h3>');
        }

        $fan_usr = get_option('fan_user');
        if (empty($fan_usr)) {
            printf('<div class="notice notice-error"><h2>Plugin-ul CurieRO FanCourier AWB nu a fost configurat.</h2><p>Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</p></div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $clientID = get_option('fan_clientID');
        $contact_exp = get_option('fan_contact_exp');
        $has_insurance = get_option('fan_asigurare');
        $plata_transport = get_option('fan_plata_transport');
        $plata_ramburs = get_option('fan_plata_ramburs');
        $numar_colete = get_option('fan_nr_colete');
        $numar_plicuri = get_option('fan_nr_plicuri');
        $deschidere = get_option('fan_deschidere');
        $sambata = get_option('fan_sambata');
        $observatii = get_option('fan_observatii');
        $personal_data = get_option('fan_personal_data');
        $fan_descriere_continut = get_option('fan_descriere_continut');
        $fan_epod_opod = get_option('fan_epod_opod');
        $force_width = get_option('fan_force_width');
        $force_height = get_option('fan_force_height');
        $force_length = get_option('fan_force_length');
        $force_weight = get_option('fan_force_weight');
        $tip_serv = get_option('fan_service');

        [
            'weight' => $weight,
            'height' => $height,
            'width' => $width,
            'length' => $length,
            'contents' => $contents,
            'packing' => $packing,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping,
        ] = curiero_extract_order_items_details($order, $fan_descriere_continut);

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

        if ($order->get_payment_method() === 'cod') {
            if ($plata_transport === 'expeditor') {
                $ramburs = $price_total;
            }

            if ($plata_transport === 'destinatar') {
                $ramburs = $price_excl_shipping;
            }
        } else {
            $ramburs = 0;
            $tip_serv = 'Standard';
        }

        $obs = $observatii;
        if ($order->get_customer_note() !== '') {
            $obs = $order->get_customer_note();
        }

        $insurance = 0;
        if ($has_insurance === 'da') {
            $insurance = $price_excl_shipping;
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO FanCourier AWB</h1><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $city = $wpdb->get_var(
            $wpdb->prepare("SELECT fan_locality_name FROM {$wpdb->prefix}curiero_localities WHERE county_initials='%s' AND locality_name='%s'", $order_details['state_short'], $order_details['city'])
        ) ?: $order_details['city'];

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        if ($fanbox = $order->get_meta('curiero_fan_fanbox', true)) {
            if (str_contains(strtolower($tip_serv), 'cont colector')) {
                $tip_serv = 'FANBox Cont Colector';
            } else {
                $tip_serv = 'FANBox';
            }
        }

        if ($collectpoint = $order->get_meta('curiero_fan_collectpoint', true)) {
            if (str_contains(strtolower($tip_serv), 'cont colector')) {
                $tip_serv = 'CollectPoint Cont Colector';
            } else {
                $tip_serv = 'CollectPoint';
            }
        }

        $awb_details = [
            'fan_id' => $clientID,
            'tip_serviciu' => $tip_serv,
            'nr_plicuri' => $numar_plicuri,
            'nr_colete' => $numar_colete,
            'greutate' => $weight,
            'plata_expeditie' => $plata_transport,
            'plata_ramburs' => $plata_ramburs,
            'ramburs' => $ramburs,
            'nume_destinatar' => $order_details['name'],
            'companie' => $order_details['company'],
            'telefon' => $order_details['phone'],
            'mail' => $order_details['email'],
            'judet' => $order_details['state_long'],
            'localitate' => $city,
            'adresa' => $address,
            'adresa_collectpoint' => $collectpoint,
            'fanbox_id' => $fanbox,
            'cod_postal' => $order_details['postcode'],
            'val_decl' => $insurance,
            'observatii' => $obs,
            'continut' => $contents,
            'deschidere_la_livrare' => empty($deschidere) ? '' : 'A',
            'livrare_sambata' => empty($sambata) ? '' : 'S',
            'packing' => $packing,
            'personal_data' => $personal_data,
            'pers_contact' => $contact_exp,
            'height' => $height,
            'width' => $width,
            'length' => $length,
            'epod_opod' => $fan_epod_opod,
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
            $trimite_mail = get_option('fan_trimite_mail');
            $parameters = self::getAwbDetails($order_id);

            $parameters = apply_filters('curiero_awb_details_overwrite', $parameters, self::$public_name, $order_id);
            $courier = CurieRO()->container->get(CurieroFanClass::class);
            $response = $courier->callMethod('generateAwb', $parameters, 'POST');

            if ($response['status'] === 200) {
                $id = json_decode($response['message']);
                if (is_numeric($id)) {
                    if ($trimite_mail === 'da') {
                        static::send_mails($order_id, $id, $parameters);
                    }

                    $order->update_meta_data(static::$awb_field, $id);
                    $order->update_meta_data('awb_fan_client_id', $parameters['fan_id']);
                    $order->update_meta_data('awb_fan_status_id', '0');
                    $order->update_meta_data('awb_fan_status', 'AWB-ul a fost inregistrat de catre clientul expeditor.');
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', static::$public_name, $id, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('fancourier_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('fancourier_account_status');
                    }
                } else {
                    set_transient('fancourier_error_msg', 'Eroare la generare AWB: ' . $id, MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('fancourier_error_msg', 'Eroare la generare AWB: ' . $response['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('fancourier_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $parameters): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $parameters['mail'];
        $email_template = get_option('fan_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_fan_email_data', [
            'awb' => $awb,
            'comanda' => $order,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'produse' => $order->get_items(),
            'total_comanda' => $parameters['ramburs'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('fan_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('fan_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_fan_email', $email_content, $data);

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

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(static::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://www.fancourier.ro/awb-tracking/?tracking=%1$s" target="_blank">Status AWB</a></p>', $awb);
        }
    }

    public static function autogenerate_awb(int $order_id): void
    {
        if (get_option('fan_auto_generate_awb') !== 'da') {
            return;
        }
        as_schedule_single_action(time(), 'curiero_fan_courier_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_fan_courier_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fan_courier_awb_update', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_fetch_fan_box')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fetch_fan_box', [], 'curiero_printing_methods');
        }
    }

    public function fetch_fan_box(): void
    {
        $fanAPI = CurieRO()->container->get(CurieroFanClass::class);
        delete_transient('curiero_fanbox_list');
        $fanAPI->getFanboxList();

        delete_transient('curiero_fan_collect_points');
        $fanAPI->getCollectPointList();
    }

    public function update_awb_status(): void
    {
        // Set the meta key and value to filter out
        $meta_key = 'awb_fan_status_id';
        $meta_values = ['S2', '2'];

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
                'value' => $meta_values,
                'compare' => 'NOT IN',
            ],
            'return' => 'ids',
        ];

        $orders = wc_get_orders($args);
        foreach (array_chunk($orders, 50) as $status_chunk) {
            as_schedule_single_action(time(), 'curiero_fan_courier_awb_update_chunk', [$status_chunk], 'curiero_printing_methods');
        }

        $obj_fan = CurieRO()->container->get(APIFanCourierClass::class);
        $bankTransfers = $obj_fan->getBankTransfers();
        foreach (array_chunk($bankTransfers, 50) as $transfer_chunk) {
            as_schedule_single_action(time(), 'curiero_fan_courier_awb_transfers_chunk', [$transfer_chunk], 'curiero_printing_methods');
        }
    }

    public function update_awb_status_chunk(array $order_ids): void
    {
        if (empty($order_ids)) {
            return;
        }

        foreach ($order_ids as $order_id) {
            $order = curiero_get_order($order_id);
            $awb_fan_for_update = $order->get_meta(static::$awb_field, true);
            if (empty($awb_fan_for_update)) {
                continue;
            }

            $parameters = [
                'awb' => [$awb_fan_for_update],
            ];

            $obj_fan = CurieRO()->container->get(APIFanCourierClass::class);
            $new_status = $obj_fan->getLatestStatus($parameters);

            if (empty($new_status)) {
                continue;
            }

            $order->update_meta_data('awb_fan_status_id', $new_status['id']);
            $order->update_meta_data('awb_fan_status', $new_status['status']);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $new_status['status'], get_option('fan_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $new_status['status']);
        }
    }

    public function update_awb_transfers_chunk(array $transfers): void
    {
        if (empty($order_ids)) {
            return;
        }

        foreach ($transfers as $transfer) {
            $orderId = curiero_get_post_id_by_meta(static::$awb_field, $transfer['numar_awb']);

            if ($orderId) {
                $order = curiero_get_order($orderId);
                $order->update_meta_data('ordin_plata_ramburs', $transfer['data_virament']);
                $order->update_meta_data('ordin_plata_ramburs_value', $transfer['suma_incasata']);
                $order->save_meta_data();
            }
        }
    }

    public function handle_option_changes(): void
    {
        $delete_transients = function (?string $new_val, ?string $old_val): ?string {
            if ($old_val !== $new_val) {
                delete_transient('curiero_fan_token');
                delete_transient('curiero_fan_client_ids');
                delete_transient('curiero_fan_services');
            }

            return $new_val;
        };

        $unset_client_id = function (?string $new_val, ?string $old_val): ?string {
            if ($old_val !== $new_val) {
                update_option('fan_clientID', '');
            }

            return $new_val;
        };

        add_filter('pre_update_option_fan_service', function (?string $new_val, ?string $old_val): ?string {
            if (
                $old_val !== $new_val
                && empty($new_val)
            ) {
                return $old_val;
            }

            return $new_val;
        }, 10, 2);

        add_filter('pre_update_option_fan_valid_auth', function (?string $new_val): ?string {
            if (
                (bool) $new_val
                && empty(get_option('fan_clientID', ''))
            ) {
                $new_clientId = @reset(CurieRO()->container->get(APIFanCourierClass::class)->getClientIds())['id'] ?? '';
                if (!empty($new_clientId)) {
                    update_option('fan_clientID', $new_clientId);
                }
            }

            return $new_val;
        }, 10, 2);

        add_filter('pre_update_option_fan_user', $unset_client_id, 10, 2);
        add_filter('pre_update_option_fan_user', $delete_transients, 10, 2);
        add_filter('pre_update_option_fan_password', $delete_transients, 10, 2);
    }
}

<?php

// Exit if accessed directly

defined('ABSPATH') || exit;

class CurieRO_Printing_Cargus extends CurieRO_Printing_Method
{
    public static $alias = 'cargus';

    public static $public_name = 'Cargus';

    public static $awb_field = 'awb_urgent_cargus';

    public function __construct()
    {
        parent::__construct();

        add_action('woocommerce_order_details_after_order_table_items', [$this, 'add_awb_notice']);

        add_action('admin_init', [$this, 'add_register_setting']);
        add_action('admin_init', [$this, 'register_as_action']);
        add_action('admin_init', [$this, 'handle_option_changes']);

        add_action('curiero_fetch_cargus_lockers', [$this, 'fetch_cargus_lockers']);

        add_action('curiero_cargus_awb_update', [$this, 'update_awb_status']);
        add_action('curiero_cargus_awb_update_chunk', [$this, 'update_awb_status_chunk']);
        add_action('curiero_cargus_awb_generate', [$this, 'generate_awb'], 10, 2);
    }

    public function add_register_setting(): void
    {
        add_option('uc_username', '');
        add_option('uc_password', '');
        add_option('uc_apikey', '');
        add_option('uc_punct_ridicare', '0');
        add_option('uc_price_table_id', '0');
        add_option('uc_nr_colete', '1');
        add_option('uc_nr_plicuri', '0');
        add_option('uc_observatii', '');
        add_option('uc_serie_client', '');
        add_option('uc_plata_transport', '1');
        add_option('uc_plata_ramburs', '1');
        add_option('uc_asigurare', '0');
        add_option('uc_deschidere', '0');
        add_option('uc_matinal', '0');
        add_option('uc_sambata', '0');
        add_option('uc_tip_serviciu', '34');
        add_option('uc_descrie_continut', '1');
        add_option('uc_trimite_mail', '0');
        add_option('uc_print_format', '0');
        add_option('uc_print_once', '0');
        add_option('uc_auto_generate_awb', 'nu');
        add_option('uc_auto_mark_complete', 'nu');
        add_option('uc_force_width', '');
        add_option('uc_force_height', '');
        add_option('uc_force_length', '');
        add_option('uc_force_weight', '');

        register_setting("{$this::$alias}_settings", 'uc_username');
        register_setting("{$this::$alias}_settings", 'uc_password');
        register_setting("{$this::$alias}_settings", 'uc_apikey');
        register_setting("{$this::$alias}_settings", 'uc_punct_ridicare');
        register_setting("{$this::$alias}_settings", 'uc_price_table_id');
        register_setting("{$this::$alias}_settings", 'uc_nr_colete');
        register_setting("{$this::$alias}_settings", 'uc_nr_plicuri');
        register_setting("{$this::$alias}_settings", 'uc_observatii');
        register_setting("{$this::$alias}_settings", 'uc_serie_client');
        register_setting("{$this::$alias}_settings", 'uc_plata_transport');
        register_setting("{$this::$alias}_settings", 'uc_plata_ramburs');
        register_setting("{$this::$alias}_settings", 'uc_asigurare');
        register_setting("{$this::$alias}_settings", 'uc_deschidere');
        register_setting("{$this::$alias}_settings", 'uc_matinal');
        register_setting("{$this::$alias}_settings", 'uc_sambata');
        register_setting("{$this::$alias}_settings", 'uc_tip_serviciu');
        register_setting("{$this::$alias}_settings", 'uc_descrie_continut');
        register_setting("{$this::$alias}_settings", 'uc_print_format');
        register_setting("{$this::$alias}_settings", 'uc_print_once');
        register_setting("{$this::$alias}_settings", 'uc_trimite_mail');
        register_setting("{$this::$alias}_settings", 'uc_auto_generate_awb');
        register_setting("{$this::$alias}_settings", 'uc_auto_mark_complete');
        register_setting("{$this::$alias}_settings", 'uc_force_width');
        register_setting("{$this::$alias}_settings", 'uc_force_height');
        register_setting("{$this::$alias}_settings", 'uc_force_length');
        register_setting("{$this::$alias}_settings", 'uc_force_weight');

        require 'templates/default_email_template.php';
    }

    public function meta_box_callback($post): void
    {
        $order = curiero_get_order($post);
        $awb = $order->get_meta(static::$awb_field, true);

        if ($awb) {
            echo '<p><input type="text" value="' . $awb . '" style="width: 80%; text-align: center; vertical-align: top;" readonly="true" autocomplete="false" /><a class="button" style="width: 19%; text-align: center;" href="https://cargus.ro/tracking-romanian/?t=' . $awb . '" target="_blank"><i class="dashicons dashicons-clipboard" style="vertical-align: middle; font-size: 17px;" title="Tracking AWB"></i></a></p>';
            echo '<p><a href="' . curiero_order_action_url('cargus', 'download', $order->get_id()) . '" class="button" target="blank_" style="width: 100%; text-align: center;"><i class="dashicons dashicons-download" style="vertical-align: middle; font-size: 17px;"></i> Descarca AWB </a></p>';
            echo '<p><a href="' . curiero_order_action_url('cargus', 'delete', $order->get_id()) . '" onclick="return confirm(`Sunteți sigur(ă) că doriți să ștergeți AWB-ul?`)" class="button secondary_button" style="width: 100%; text-align: center;"><i class="dashicons dashicons-trash" style="vertical-align: sub; font-size: 17px;"></i> Sterge AWB </a></p>';
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
        $status = $order->get_meta('awb_urgent_cargus_trace_status', true);
        $ordin_plata_ramburs = $order->get_meta('op_urgent_cargus', true);
        $ordin_plata_ramburs_value = $order->get_meta('op_urgent_cargus_value', true);

        if (!empty($awb)) {
            $printing_link = curiero_order_action_url('cargus', 'download', $order->get_id());
            echo '<a class="button tips downloadBtn" href="' . $printing_link . '" target="_blank" data-tip="Printeaza" style="background-color:#fffcee">' . $awb . '</a><br>';
            echo '<div class="curieroAWBNoticeWrapper">';

            if (!empty($status)) {
                if ($status == 'Confirmat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-yes"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Preluat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons dashicons-migrate"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Rambursat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-images-alt"></span>Status: ' . $status . '<br></div>';
                } elseif ($status == 'Returnat') {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-image-rotate"></span>Status: ' . $status . '<br></div>';
                } else {
                    echo '<div class="curieroAWBNotice"><span class="dashicons dashicons-dismiss"></span>Status: ' . $status . '<br></div>';
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
            if (get_option('uc_nr_colete') <= 1) {
                echo '<p><button type="button" class="button tips generateBtn" data-tip="' . __('Genereaza AWB Cargus', 'curiero-plugin') . '" data-courier="' . self::$alias . '" data-order_id="' . $order->get_id() . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/cargus_logo.svg" height="29"/></button></p>';
            } else {
                echo '<p><a class="button generateBtn tips" data-tip="' . __('Genereaza AWB Cargus', 'curiero-plugin') . '" href="' . curiero_build_url("admin.php?page=generate-awb-urgent-cargus&order_id={$order->get_id()}") . '"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/cargus_logo.svg" height="29" /></a></p>';
            }
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

        $UserName = rawurlencode(get_option('uc_username'));
        $punct_ridicare = get_option('uc_punct_ridicare');
        $price_table_id = get_option('uc_price_table_id');
        $numar_plicuri = get_option('uc_nr_plicuri');
        $numar_colete = get_option('uc_nr_colete');
        $plata_transport = get_option('uc_plata_transport');
        $plata_ramburs = get_option('uc_plata_ramburs');
        $has_insurance = get_option('uc_asigurare');
        $deschidere = get_option('uc_deschidere');
        $service_type_id = get_option('uc_tip_serviciu');
        $matinal = get_option('uc_matinal');
        $sambata = get_option('uc_sambata');
        $descrie_continut = get_option('uc_descrie_continut');
        $observatii = get_option('uc_observatii');
        $serie_client = get_option('uc_serie_client');
        $force_width = get_option('uc_force_width');
        $force_height = get_option('uc_force_height');
        $force_length = get_option('uc_force_length');
        $force_weight = get_option('uc_force_weight');

        if (empty($UserName)) {
            printf('<div class="wrap"><h1>CurieRO UrgentCargus AWB</h2><br><h2>Plugin-ul CurieRO UrgentCargus AWB nu a fost configurat.</h2> Va rugam dati click <a href="%s"> aici</a> pentru a il configura.</div>', curiero_build_url('admin.php', ['page' => static::$alias . '_settings']));
            wp_die();
        }

        $order_details = curiero_extract_order_details($order);

        if (empty($order_details['address_full'])) {
            wp_die('<div class="wrap"><h1>CurieRO UrgentCargus AWB</h2><br><h2>Eroare: Nu au fost completate datele de livrare ale destinatarului.</h2></div>');
        }

        $city = $wpdb->get_var(
            $wpdb->prepare("SELECT cargus_locality_name FROM {$wpdb->prefix}curiero_localities WHERE county_initials='%s' AND locality_name='%s'", $order_details['state_short'], $order_details['city'])
        ) ?: $order_details['city'];

        if (str_contains(strtolower($city), 'sector')) {
            $city = 'BUCURESTI';
        }

        [
            'contents' => $contents,
            'height' => $height,
            'width' => $width,
            'length' => $length,
            'weight' => $weight,
            'price_total' => $price_total,
            'price_excl_shipping' => $price_excl_shipping
        ] = curiero_extract_order_items_details($order, $descrie_continut);

        $contents = substr($contents, 0, 512);
        if (empty($contents)) {
            $contents = 'n/a';
        }

        if ($order->get_payment_method() === 'cod') {
            $ramburs = $price_total;
        } else {
            $ramburs = 0;
        }

        $insurance = 0;
        if ($has_insurance == '1') {
            $insurance = $price_excl_shipping;
        }

        $obs = $observatii;
        if ($order->get_customer_note() != '') {
            $obs = $order->get_customer_note();
        }

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

        $colete_parcel_codes = [];
        if (!empty($numar_colete)) {
            $colete_parcel_codes[] = [
                'Code' => 0,
                'Type' => 1,
                'Length' => (int) $length,
                'Width' => (int) $width,
                'Height' => (int) $height,
                'Weight' => (int) $weight,
            ];
        }

        $plicuri_parcel_codes = [];
        if (!empty($numar_plicuri)) {
            for ($i = 0; $i < $numar_plicuri; ++$i) {
                $plicuri_parcel_codes[] = [
                    'Code' => $i + count($colete_parcel_codes ?? []),
                    'Type' => 0,
                ];
            }
        }

        if (empty($numar_colete) && !empty($numar_plicuri)) {
            $weight = 1;
        }

        $address = $order_details['address_full'];
        if (curiero_string_contains_array_element(strtolower($address), ['easybox', 'dpdbox', 'fanbox', 'paypoint', 'punct ', 'locker '])) {
            $address = $order->get_meta('original_shipping_address', true) ?: trim("{$order->get_billing_address_1()} {$order->get_billing_address_2()}");
        }

        $otherRepayment = '';
        if ($order->get_meta('cargus_swap_package_checkbox', true)) {
            $otherRepayment = 'colet la schimb';
        }

        $awbsDetails = [
            'Sender' => [
                'LocationId' => $punct_ridicare,
            ],
            'Recipient' => [
                'Name' => empty($order_details['company']) ? $order_details['name'] : $order_details['company'],
                'CountyName' => $order_details['state_short'],
                'LocalityName' => $city,
                'AddressText' => $address,
                'ContactPerson' => $order_details['name'],
                'PhoneNumber' => $order_details['phone'],
                'Email' => $order_details['email'],
                'CodPostal' => $order_details['postcode'],
            ],
            'ParcelCodes' => array_merge($colete_parcel_codes, $plicuri_parcel_codes),
            'Parcels' => (int) $numar_colete,
            'Envelopes' => (int) $numar_plicuri,
            'TotalWeight' => (int) $weight,
            'DeclaredValue' => (float) $insurance,
            'CashRepayment' => 0,
            'BankRepayment' => (float) $ramburs,
            'OtherRepayment' => $otherRepayment,
            'ServiceId' => (int) $service_type_id,
            'OpenPackage' => filter_var($deschidere, FILTER_VALIDATE_BOOLEAN),
            'PriceTableId' => ($price_table_id == 1) ? 0 : $price_table_id,
            'ShipmentPayer' => (int) $plata_transport,
            'ShippingRepayment' => (int) $plata_ramburs,
            'SaturdayDelivery' => filter_var($sambata, FILTER_VALIDATE_BOOLEAN),
            'MorningDelivery' => filter_var($matinal, FILTER_VALIDATE_BOOLEAN),
            'Observations' => $obs,
            'PackageContent' => $contents ? $contents : null,
            'CustomString' => $serie_client,
            'SenderReference1' => '',
            'RecipientReference1' => '',
            'RecipientReference2' => '',
            'InvoiceReference' => 'Comanda nr. ' . $order_details['number'],
            'SenderClientId' => '',
        ];

        if ($lockerId = $order->get_meta('curiero_cargus_locker', true)) {
            $awbsDetails['DeliveryPudoPoint'] = $lockerId;
            $awbsDetails['ServiceId'] = 38;
            $awbsDetails['ShipmentPayer'] = 1;
            $awbsDetails['SaturdayDelivery'] = false;
            $awbsDetails['OpenPackage'] = false;
        }

        $awbsDetails = apply_filters('curiero_awb_details', $awbsDetails, self::$public_name, $order);
        $awbsDetails = array_map('curiero_remove_accents', $awbsDetails);

        return $awbsDetails;
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
            $awbsDetails = self::getAwbDetails($order_id);
            $trimite_mail = get_option('uc_trimite_mail');
            $awbsDetails = apply_filters('curiero_awb_details_overwrite', $awbsDetails, self::$public_name, $order_id);

            $courier = CurieRO()->container->get(CurieroUCClass::class);
            $result = $courier->callMethod('generateAwb', $awbsDetails, 'POST');

            if ($result['status'] === 200) {
                if (is_numeric(json_decode($result['message']))) {
                    $awb = json_decode($result['message']);

                    if ($trimite_mail == '1') {
                        static::send_mails($order_id, $awb, $awbsDetails);
                    }

                    $order->update_meta_data(self::$awb_field, $awb);
                    $order->save_meta_data();

                    do_action('curiero_awb_generated', self::$public_name, $awb, $order_id);

                    $account_status_response = $courier->callMethod('newAccountStatus', [], 'POST');
                    $account_status = json_decode($account_status_response['message']);

                    if ($account_status->show_message) {
                        set_transient('cargus_account_status', $account_status->message, MONTH_IN_SECONDS);
                    } else {
                        delete_transient('cargus_account_status');
                    }
                } else {
                    set_transient('cargus_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
                }
            } else {
                set_transient('cargus_error_msg', 'Eroare la generare AWB: ' . $result['message'], MINUTE_IN_SECONDS);
            }
        } catch (Exception $e) {
            set_transient('cargus_error_msg', 'Eroare la generare AWB: ' . $e->getMessage(), MINUTE_IN_SECONDS);
        }
    }

    public static function send_mails(int $order_id, string $awb, array $awbsDetails): void
    {
        $wc_mail = WC_Emails::instance();
        add_filter('woocommerce_email_content_type', function () {
            return 'text/html';
        });

        $receiver_email = $awbsDetails['Recipient']['Email'];
        $email_template = get_option('uc_email_template');

        $order = curiero_get_order($order_id);
        $data = apply_filters('curiero_overwrite_cargus_email_data', [
            'awb' => $awb,
            'nr_comanda' => $order->get_order_number(),
            'data_comanda' => $order->get_date_created()->format('d.m.Y H:i'),
            'comanda' => $order,
            'produse' => $order->get_items(),
            'total_comanda' => $awbsDetails['BankRepayment'],
        ]);

        $subiect_mail = curiero_handle_email_template(get_option('uc_subiect_mail'), $data);
        $titlu_mail = apply_filters('curiero_overwrite_titlu_mail', get_option('uc_titlu_mail'), static::$public_name);

        $email_content = curiero_handle_email_template($email_template, $data);
        $email_content = $wc_mail->wrap_message($titlu_mail, $email_content);
        $email_content = apply_filters('curiero_overwrite_cargus_email', $email_content, $data);

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
        if (get_option('uc_auto_generate_awb') !== 'da') {
            return;
        }

        as_schedule_single_action(time(), 'curiero_cargus_awb_generate', [$order_id, true], 'curiero_printing_methods');
    }

    public function register_as_action(): void
    {
        if (false === as_next_scheduled_action('curiero_cargus_awb_update')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_cargus_awb_update', [], 'curiero_printing_methods');
        }

        if (false === as_next_scheduled_action('curiero_fetch_cargus_lockers')) {
            as_schedule_recurring_action(time(), 12 * HOUR_IN_SECONDS, 'curiero_fetch_cargus_lockers', [], 'curiero_printing_methods');
        }
    }

    public function fetch_cargus_lockers(): void
    {
        delete_transient('cargus_lockers');
        CurieRO()->container->get(CurieroUCClass::class)->getPudoPoints();
    }

    public function add_awb_notice(WC_Abstract_Order $order): void
    {
        $awb = $order->get_meta(self::$awb_field, true);
        if ($awb) {
            printf('<p>Nota de transport (AWB) are numarul: %1$s si poate fi urmarita aici: <a href="https://cargus.ro/tracking-romanian/?t=%1$s" target="_blank">Status AWB</a></p>', $awb);
        }
    }

    public function update_awb_status(): void
    {
        $FromDate = date('Y-m-d\TH:i:s', strtotime('-24 hour'));
        $ToDate = date('Y-m-d\TH:i:s');

        $resultTrace = CurieRO()->container->get(UrgentCargusAPI::class)->callMethod('AwbStatus/GetAwbSyncStatus', [
            'FromDate' => $FromDate,
            'ToDate' => $ToDate,
        ], 'GET');

        try {
            if ($resultTrace['status'] !== 200) {
                throw new Exception('Eroare la preluarea statusului AWB-urilor.');
            }

            $resultMessage = $resultTrace['message'];
            $arrayResultTrace = json_decode($resultMessage, true, 512, JSON_THROW_ON_ERROR);

            $arrayResultTrace = array_map(function (array $item): array {
                return [
                    'BarCode' => $item['BarCode'],
                    'StatusExpression' => $item['StatusExpression'],
                    'DeductionId' => $item['DeductionId'],
                    'RepaymentValue' => $item['RepaymentValue'],
                ];
            }, $arrayResultTrace);

            foreach (array_chunk($arrayResultTrace, 40) as $chunk) {
                as_schedule_single_action(time(), 'curiero_cargus_awb_update_chunk', [$chunk], 'curiero_printing_methods');
            }
        } catch (Exception $e) {
            as_schedule_single_action(strtotime('+5 minutes'), 'curiero_cargus_awb_update', [], 'curiero_printing_methods_retry', true);
        }
    }

    public function update_awb_status_chunk(array $awb_statuses): void
    {
        if (empty($awb_statuses)) {
            return;
        }

        foreach ($awb_statuses as $awbTrace) {
            $awbBarCode = $awbTrace['BarCode'];
            $awbStatus = $awbTrace['StatusExpression'];
            $awbDeductionId = $awbTrace['DeductionId'];
            $RepaymentValue = $awbTrace['RepaymentValue'];

            $order_id = curiero_get_post_id_by_meta(self::$awb_field, $awbBarCode);
            if (empty($order_id)) {
                continue;
            }

            $order = curiero_get_order($order_id);

            if ($order->get_meta('awb_urgent_cargus_trace_status', true) === $awbStatus) {
                continue;
            }

            $order->update_meta_data('awb_urgent_cargus_trace_status', $awbStatus);
            $order->update_meta_data('op_urgent_cargus', $awbDeductionId);
            $order->update_meta_data('op_urgent_cargus_value', $RepaymentValue);
            $order->save_meta_data();

            curiero_mark_order_complete($order_id, $awbStatus, get_option('uc_auto_mark_complete', 'nu'));
            curiero_autogenerate_invoice($order_id, $awbStatus);
        }
    }

    public function handle_option_changes(): void
    {
        $delete_token = function (?string $new_val, ?string $old_val): ?string {
            if ($old_val !== $new_val) {
                delete_transient('curiero_cargus_token');
            }

            return $new_val;
        };

        add_filter('pre_update_option_uc_username', $delete_token, 10, 2);
        add_filter('pre_update_option_uc_password', $delete_token, 10, 2);
        add_filter('pre_update_option_uc_apikey', $delete_token, 10, 2);
    }
}

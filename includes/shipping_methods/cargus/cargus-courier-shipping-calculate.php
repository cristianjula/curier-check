<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function Cargus_Shipping_Method(): void
{
    if (!class_exists('Cargus_Shipping_Method')) {
        class Cargus_Shipping_Method extends WC_Shipping_Method
        {
            public $locker_list;

            public function __construct()
            {
                $this->id = 'urgentcargus_courier';
                $this->method_title = __('Cargus Shipping', 'curiero-plugin');
                $this->method_description = __('Cargus Shipping Method for courier', 'curiero-plugin');

                $this->availability = 'including';
                $this->countries = ['RO'];

                $this->init();

                $this->title = $this->get_option('title');

                $this->locker_list = collect()->lazy();
            }

            public function init(): void
            {
                $this->init_form_fields();
                $this->init_settings();

                add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
            }

            public function init_form_fields(): void
            {
                $this->form_fields = [
                    'title' => [
                        'title' => __('Denumire metoda livrare', 'curiero-plugin'),
                        'type' => 'text',
                        'description' => __('Denumirea metodei de livrare in Cart si Checkout, vizibila de catre client.', 'curiero-plugin'),
                        'default' => __('Cargus', 'curiero-plugin'),
                        'desc_tip' => true,
                    ],
                    'tarif_contract' => [
                        'title' => __('Afisare tarif contract', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'description' => __('Pentru a activa aceasta optiune trebuie sa aveti si metoda Cargus - AWB activata si configurata.', 'curiero-plugin'),
                        'desc_tip' => true,
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                            'estimate_bigger' => __('Daca costul de livrare este mai mare decat suma fixa', 'curiero-plugin'),
                        ],
                    ],
                    'prag_gratis_cupoane' => [
                        'title' => __('Include cupoane in pragul gratis', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'yes',
                        'css' => 'width:400px;',
                        'description' => __('Include/exclude contravaloarea cupoanelor in pragul de gratuitate.', 'curiero-plugin'),
                        'desc_tip' => true,
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                    ],
                    'prag_gratis_Bucuresti' => [
                        'title' => __('Prag gratis Bucuresti', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('250', 'curiero-plugin'),
                    ],
                    'suma_fixa_Bucuresti' => [
                        'title' => __('Suma fixa Bucuresti', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('15', 'curiero-plugin'),
                    ],
                    'prag_gratis_provincie' => [
                        'title' => __('Prag gratis provincie', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('250', 'curiero-plugin'),
                    ],
                    'suma_fixa_provincie' => [
                        'title' => __('Suma fixa provincie', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('18', 'curiero-plugin'),
                    ],
                    'ignore_extra_km' => [
                        'title' => __('Ignora cost KM suplimentari la livrare gratuita', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'description' => __('Atunci cand este atins pragul de livrare gratuita, aceasta optiune ignora costul de KM suplimentari la calcularea tarifiului de livrare.', 'curiero-plugin'),
                        'desc_tip' => true,
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                    ],
                    'pret_km_suplimentar' => [
                        'title' => __('Pret KM suplimentar', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('1', 'curiero-plugin'),
                    ],
                    'pret_kg_suplimentar' => [
                        'title' => __('Pret KG suplimentar', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Pretul de KG suplimentar va fi adaugat la suma fixa, daca doriti sa folositi doar pretul fix, setati acest camp pe 0.', 'curiero-plugin'),
                    ],
                    'prag_gratis_kg' => [
                        'title' => __('Prag KG gratuite', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Pretul pe KG suplimentar va fi adaugat la suma fixa daca greutatea totala a comenzii depaseste pragul de kilograme gratuit.', 'curiero-plugin'),
                    ],
                    'tarif_implicit' => [
                        'title' => __('Tarif implicit', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Tariful implicit pentru metoda de livrare, pentru a arata o suma atunci cand nu este introdusa adresa de livrare. ', 'curiero-plugin'),
                    ],
                    'tarif_maxim' => [
                        'title' => __('Tarif maxim livrare', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('40', 'curiero-plugin'),
                        'description' => __('Plafonare tarif de livrare. Clientul nu plateste mai mult decat suma specificata', 'curiero-plugin'),
                        'desc_tip' => true,
                    ],
                    'tip_ramburs' => [
                        'title' => __('Tip ramburs', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Cont',
                        'css' => 'width:400px;',
                        'options' => [
                            'Cont' => __('Cont colector', 'curiero-plugin'),
                            'Cash' => __('Numerar', 'curiero-plugin'),
                        ],
                        'description' => __('La cash rambursul vine in plic, la cont vine in contul din contractul semnat', 'curiero-plugin'),
                        'desc_tip' => true,
                    ],
                    'colet_la_schimb' => [
                        'title' => __('Optiune "Colet la schimb"', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'options' => [
                            'yes' => __('Da', 'curiero-plugin'),
                            'no' => __('Nu', 'curiero-plugin'),
                        ],
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a selecta optiunea "colet la schimb".', 'curiero-plugin'),
                        'desc_tip' => true,
                    ],
                    'colet_la_schimb_field_title' => [
                        'title' => __('Denumire camp "Colet la schimb" in Checkout', 'curiero-plugin'),
                        'type' => 'text',
                        'default' => __('Cargus "Colet la schimb"', 'curiero-plugin'),
                    ],
                    'ship_and_go' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Activeza serviciul Ship & Go', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'options' => [
                            'yes' => __('Da', 'curiero-plugin'),
                            'no' => __('Nu', 'curiero-plugin'),
                        ],
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a selecta optiunea Ship & Go. Nu veti putea genera AWB daca nu aveti cont premium activ.', 'curiero-plugin'),
                        'desc_tip' => true,
                    ],
                    'ship_and_go_field_title' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Denumire camp Ship & Go in Checkout', 'curiero-plugin'),
                        'type' => 'text',
                        'default' => __('Cargus Ship & Go', 'curiero-plugin'),
                    ],
                    'tarif_ship_and_go' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Suma fixa pentru Ship & Go', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => 15,
                        'desc_tip' => true,
                        'description' => __('Daca doriti ca pretul sa fie completat automat cu tariful de contract, lasati acesti camp gol sau 0.', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'prag_gratis_ship_and_go' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Prag gratis Ship & Go', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => 200,
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'ship_and_go_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Modifica adresa de livrare cu adresa Ship & Go', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica punctului Ship & Go ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - Cargus Livrare: Ghid de completare a setărilor de livrare.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare Cargus {$help_tip}</h2>
                    <table class="form-table wp-list-table widefat striped" style="max-width: 850px;">
                        <thead>
                            <tr>
                                <th class="wc-shipping-class-name">Optiune</th>
                                <th class="wc-shipping-class-slug"></th>
                            </tr>
                        </thead>
                        <tbody class="wc-shipping-class-rows">
                            {$this->generate_settings_html($this->get_form_fields(), false)}
                        </tbody>
                    </table>
                HTML;
            }

            public function calculate_shipping($package = []): void
            {
                global $wpdb;

                $judetdest = $package['destination']['state'] ?? '';
                $orasdest = $package['destination']['city'] ?? '';

                $package_details = curiero_calculate_package_details($this, $package);
                if (empty($package_details)) {
                    return;
                }

                $tarif_contract = $this->get_option('tarif_contract');
                $ignore_extra_km = $this->get_option('ignore_extra_km');

                if ($judetdest === 'B') {
                    $prag_gratis = curiero_string_to_float($this->get_option('prag_gratis_Bucuresti'));
                    $suma_fixa = curiero_string_to_float($this->get_option('suma_fixa_Bucuresti'));
                } else {
                    $prag_gratis = curiero_string_to_float($this->get_option('prag_gratis_provincie'));
                    $suma_fixa = curiero_string_to_float($this->get_option('suma_fixa_provincie'));
                }

                $tarif_maxim = $this->get_option('tarif_maxim') ?: 99999;
                $tarif_implicit = curiero_string_to_float($this->get_option('tarif_implicit'));
                $ignoreMethod = false;

                $localityList = $wpdb->get_results(
                    $wpdb->prepare("SELECT cargus_extra_km, cargus_locality_name FROM {$wpdb->prefix}curiero_localities WHERE cargus_locality_id IS NOT NULL AND county_initials='%s' AND locality_name='%s' LIMIT 1", $judetdest, $orasdest)
                );

                $extra_km = !empty($localityList) ? $localityList[0]->cargus_extra_km : 0;

                if (
                    (
                        ($extra_km == 0 || $ignore_extra_km === 'yes')
                        && $package_details['cartValue'] >= $prag_gratis
                    ) || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                } elseif (
                    empty($orasdest) || empty($localityList)
                ) {
                    $transport = $tarif_implicit;
                    $ignoreMethod = ($transport == 0);
                } else {
                    if ($tarif_contract !== 'no' && class_exists('CurieRO_Printing_Cargus')) {
                        if (get_option('uc_asigurare') === '0') {
                            $valoare_declarata = 0;
                        } else {
                            $valoare_declarata = $package_details['declaredValue'];
                        }

                        $tipramburs = $this->get_option('tip_ramburs');
                        if (WC()->session->get('chosen_payment_method') !== 'cod') {
                            $package_details['codValue'] = 0;
                        }

                        if ($tipramburs === 'Cash') {
                            $ramburs_cash = $package_details['codValue'];
                            $ramburs_cont = 0;
                        } else {
                            $ramburs_cash = 0;
                            $ramburs_cont = $package_details['codValue'];
                        }

                        $orasexp = WC()->countries->get_base_city();
                        $judetexp = WC()->countries->get_base_state();

                        $id_tarif = get_option('uc_price_table_id');

                        $request_params = apply_filters('curiero_shipping_calculate_contract', [
                            'FromCountyName' => $judetexp,
                            'FromLocalityName' => $orasexp,
                            'ToCountyName' => $judetdest,
                            'ToLocalityName' => $localityList[0]->cargus_locality_name,
                            'Parcels' => (int) get_option('uc_nr_colete'),
                            'Envelopes' => (int) get_option('uc_nr_plicuri'),
                            'TotalWeight' => $package_details['weight'],
                            'DeclaredValue' => $valoare_declarata,
                            'CashRepayment' => $ramburs_cash,
                            'BankRepayment' => $ramburs_cont,
                            'OpenPackage' => filter_var(get_option('uc_deschidere'), FILTER_VALIDATE_BOOLEAN),
                            'MorningDelivery' => filter_var(get_option('uc_matinal'), FILTER_VALIDATE_BOOLEAN),
                            'SaturdayDelivery' => filter_var(get_option('uc_sambata'), FILTER_VALIDATE_BOOLEAN),
                            'PriceTableId' => ((int) $id_tarif) === 1 ? 0 : $id_tarif,
                            'ShipmentPayer' => (int) get_option('uc_plata_transport'),
                            'ServiceId' => $this->get_service_id($package_details['weight']),
                        ], 'Cargus');

                        try {
                            $resultShippingCalculation = CurieRO()->container->get(UrgentCargusAPI::class)->callMethod('ShippingCalculation', $request_params);

                            if ($resultShippingCalculation['status'] === 200) {
                                $jsonShippingCalculationList = $resultShippingCalculation['message'];
                                $cost = json_decode($jsonShippingCalculationList, true);

                                if (!empty($cost['Error'])) {
                                    throw new Exception($cost['Error']);
                                }

                                $transport = $cost['GrandTotal'];
                            } else {
                                throw new Exception('Eroare la calcularea tarifului de livrare.');
                            }
                        } catch (Exception $e) {
                            $transport = $tarif_implicit;
                            $ignoreMethod = ($transport == 0);
                        }

                        if ($tarif_contract === 'estimate_bigger') {
                            $transport = max($transport, $suma_fixa);
                        }
                    } else {
                        $transport = curiero_calculate_self_shipping_costs($this, $package_details, $extra_km);
                    }
                }

                if ($ignoreMethod || !is_numeric($transport)) {
                    return;
                }

                $transport = min($transport, $tarif_maxim);

                $label = $this->title;
                if ($transport == 0) {
                    $label = $this->title . ': Gratuit';
                }

                $args = [
                    'id' => $this->id,
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_cargus_shipping', $args, $judetdest, $extra_km, $orasdest);
                $this->add_rate($args);

                if (
                    $this->get_option('ship_and_go') === 'yes'
                    && $this->get_locker_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->add_locker_rate($transport, $package, $package_details);
                }
            }

            public function add_locker_rate(string $transport, array $package, array $package_details): void
            {
                $tarif_ship_and_go = $this->get_option('tarif_ship_and_go');
                if (!empty($tarif_ship_and_go)) {
                    $transport = $tarif_ship_and_go;
                }

                $prag_gratis_ship_and_go = $this->get_option('prag_gratis_ship_and_go');
                if (
                    (!empty($prag_gratis_ship_and_go) && $package_details['cartValue'] >= $prag_gratis_ship_and_go)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = $this->get_option('ship_and_go_field_title');
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => $this->id . '_ship_and_go',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_ship_and_go_shipping', $args);
                $this->add_rate($args);
            }

            public function get_locker_list(string $county, string $city): LazyCollection
            {
                if ($this->locker_list->isNotEmpty()) {
                    return $this->locker_list;
                }

                $locker_list = collect(
                    CurieRO()->container->get(CurieroUCClass::class)->getPudoPoints()
                )->lazy()->remember();

                if ($locker_list->isEmpty()) {
                    return $locker_list;
                }

                if (strlen($county) <= 2) {
                    $county = curiero_get_counties_list($county);
                }

                $this->locker_list = $locker_list
                    ->where('County', $county)
                    ->where('City', $city)
                    ->sortBy('Name', SORT_NATURAL | SORT_FLAG_CASE);

                if ($this->locker_list->isEmpty()) {
                    $this->locker_list = $locker_list
                        ->where('County', $county)
                        ->sortBy('City', SORT_NATURAL | SORT_FLAG_CASE);
                }

                return $this->locker_list;
            }

            private function get_service_id(float $weight): int
            {
                $service = (int) get_option('uc_tip_serviciu');

                if (apply_filters('curiero_cargus_shipping_service_weight_bypass', false)) {
                    return $service;
                }

                if (34 === $service) {
                    if ($weight <= 31) {
                        return 34;
                    } elseif ($weight <= 50) {
                        return 35;
                    } else {
                        return 36;
                    }
                } elseif (39 === $service) {
                    if ($weight <= 31) {
                        return 39;
                    } elseif ($weight <= 50) {
                        return 40;
                    } else {
                        return 36;
                    }
                }

                return $service;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'Cargus - Livrare',
        'Cargus - Livrare',
        curiero_manage_options_capability(),
        'urgent_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=urgentcargus_courier'));
            exit;
        }
    );
});

// Colet la schimb
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('urgentcargus_courier', 'colet_la_schimb', 'no')) {
        return;
    }

    add_action('woocommerce_review_order_after_shipping', function (): void {
        $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];
        if (WC()->session->get('chosen_shipping_methods', [])[0] === 'urgentcargus_courier') {
            echo '<tr><td colspan="2" style="text-align: left;">';
            woocommerce_form_field('cargus_swap_package_checkbox', [
                'type' => 'checkbox',
                'class' => ['input-checkbox'],
                'label' => $shipping_method->get_option('colet_la_schimb_field_title', 'Cargus "Colet la schimb"'),
            ], WC()->checkout->get_value('cargus_swap_package_checkbox'));
            echo '</td></tr>';
        }
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['cargus_swap_package_checkbox'])) {
            $order = curiero_get_order($order_id);
            $order->update_meta_data('cargus_swap_package_checkbox', true);
            $order->save_meta_data();
        }
    });

    add_action('woocommerce_admin_order_data_after_billing_address', function (WC_Abstract_Order $order): void {
        if ($order->get_meta('cargus_swap_package_checkbox', true)) {
            echo '<p><strong>Colet la schimb - Cargus: </strong><br><span style="color:green;">Activat</span></p>';
        } else {
            echo '<p><strong>Colet la schimb - Cargus: </strong><br><span style="color:red;">Dezactivat</span></p>';
        }
    });
});

// Ship & Go
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('urgentcargus_courier', 'ship_and_go', 'no')) {
        return;
    }

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);
        $current_locker_id = $form_data['curiero_cargus_locker'] ?? null;
        if ($current_locker_id && in_array('urgentcargus_courier_ship_and_go', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];
            $locker_list = $shipping_method->get_locker_list($shipping_county, $shipping_city);

            $current_locker = $locker_list->firstWhere('Id', $current_locker_id);
            WC()->session->set('curiero_cargus_locker_selected', $current_locker);
        }

        return $posted_data;
    });

    add_filter('woocommerce_available_payment_gateways', function (array $available_gateways): array {
        if ($session = WC()->session) {
            $chosen_shipping_methods = $session->get('chosen_shipping_methods');
            $current_locker = $session->get('curiero_cargus_locker_selected');
            if ($current_locker) {
                $supportedPayment = $current_locker['ServiceCOD'] ?? 0;
                if (in_array('urgentcargus_courier_ship_and_go', $chosen_shipping_methods) && $supportedPayment == 0) {
                    unset($available_gateways['cod']);
                    $message = 'La punctul Ship & Go selectat nu se pot face plati ramburs (nu se poate plati cu cardul la ridicarea comenzii).';
                    if (!wc_has_notice($message, 'notice')) {
                        wc_add_notice($message, 'notice');
                    }
                }
            }
        }

        return $available_gateways;
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_cargus_locker'])) {
            $current_locker = WC()->session->get('curiero_cargus_locker_selected');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_cargus_locker', $current_locker['Id']);

            $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];
            if ($shipping_method && $shipping_method->get_option('ship_and_go_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_locker['Name'], $current_locker['Address']);
            }

            $order->save();
            WC()->session->set('curiero_cargus_locker_selected', null);
        }
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_cargus_locker = WC()->session->get('curiero_cargus_locker_selected');

        if (
            curiero_is_session_shipping_method('urgentcargus_courier_ship_and_go')
            && empty($curiero_cargus_locker)
        ) {
            wc_add_notice(__('Va rugam sa selectati punctul Ship & Go dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('urgentcargus_courier_ship_and_go')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['urgentcargus_courier'];
        $locker_list = $shipping_method->get_locker_list($shipping_county, $shipping_city);

        $template_data['lockers'] = apply_filters('curiero_ship_and_go_list_filter', $locker_list);
        $template_data['current_locker'] = WC()->session->get('curiero_cargus_locker_selected');
        $template_data['current_locker_exists'] = $template_data['lockers']->contains($template_data['current_locker']);

        $notice_message = 'Punctul Ship & Go selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_locker_exists'] && !empty($template_data['current_locker']) && !wc_has_notice($notice_message, 'error')) {
            WC()->session->set('curiero_cargus_locker_selected', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-lockers-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });
});

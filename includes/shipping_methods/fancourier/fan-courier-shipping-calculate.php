<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function Fan_Shipping_Method(): void
{
    if (!class_exists('Fan_Shipping_Method')) {
        class Fan_Shipping_Method extends WC_Shipping_Method
        {
            public $fanbox_list;

            public $collectpoint_list;

            public function __construct()
            {
                $this->id = 'fan';
                $this->method_title = __('FanCourier Shipping', 'fan');
                $this->method_description = __('FanCourier Shipping Method for courier', 'fan');

                $this->availability = 'including';
                $this->countries = ['RO'];

                $this->init();

                $this->title = $this->get_option('title');

                $this->fanbox_list = collect()->lazy();
                $this->collectpoint_list = collect()->lazy();
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
                        'default' => __('Fan Courier', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['required' => 'required'],
                    ],
                    'tarif_contract' => [
                        'title' => __('Afisare tarif contract', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'description' => __('Pentru a activa aceasta optiune trebuie sa aveti si metoda FanCourier - AWB activata si configurata.', 'curiero-plugin'),
                        'css' => 'width:400px;',
                        'desc_tip' => true,
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                            'estimate_bigger' => __('Da, daca costul livrarii este mai mare decat suma fixa', 'curiero-plugin'),
                        ],
                    ],
                    'tarif_contract_tva' => [
                        'title' => __('Adauga cota TVA pentru tarif contract', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['step' => '0.1', 'min' => '0'],
                        'description' => __('Adauga % TVA atunci cand este folosit Tariful din contract - Acest pret vine fara TVA de la FanCourier', 'curiero-plugin'),
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
                        'desc_tip' => true,
                        'description' => __('Pretul de KM suplimentar va fi adaugat la suma fixa, daca doriti sa folositi doar pretul fix, setati acest camp pe 0.', 'curiero-plugin'),
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
                        'desc_tip' => true,
                        'description' => __('Tariful final nu poate depasi aceasta valoare', 'curiero-plugin'),
                    ],
                    'collectpoint_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza serviciul Collect Point', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege livrarea catre un Collect Point Fancourier. Setarea va fi ignorata daca folositi un cont CurieRO FanCourier gratuit.', 'curiero-plugin'),
                    ],
                    'tarif_collectpoints' => [
                        'title' => __('<span style="color:red">[Premium]</span> Suma fixa pentru Collect Point', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Daca doriti ca pretul sa fie completat automat cu tariful de contract, lasati acesti camp gol sau 0.', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'prag_gratis_collectpoints' => [
                        'title' => __('<span style="color:red">[Premium]</span> Prag gratis Collect Point', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('250', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'collectpoint_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span> Modifica adresa de livrare cu adresa Collectpoint', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica Collectpoint-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                    'fanbox_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza serviciul FANBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege livrarea catre un Collect Point Fancourier. Setarea va fi ignorata daca folositi un cont CurieRO FanCourier gratuit.', 'curiero-plugin'),
                    ],
                    'tarif_fanbox' => [
                        'title' => __('<span style="color:red">[Premium]</span> Suma fixa pentru FANBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Daca doriti ca pretul sa fie completat automat cu tariful de contract, lasati acesti camp gol sau 0.', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'fanbox_map_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza Harta FANBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege un FANBox prin intermediul hartii.', 'curiero-plugin'),
                    ],
                    'prag_gratis_fanbox' => [
                        'title' => __('<span style="color:red">[Premium]</span> Prag gratis FANBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('250', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'fanbox_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span> Modifica adresa de livrare cu adresa FANBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica FANBox-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - FAN Courier Livrare: Ghid de completare a setărilor de livrare.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare FanCourier {$help_tip}</h2>
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

                $tarif_contract_tva = curiero_string_to_float($this->get_option('tarif_contract_tva'));
                $tarif_maxim = curiero_string_to_float($this->get_option('tarif_maxim') ?: 99999);
                $tarif_implicit = curiero_string_to_float($this->get_option('tarif_implicit'));
                $ignoreMethod = false;

                $localityList = $wpdb->get_results(
                    $wpdb->prepare("SELECT fan_locality_name, fan_extra_km FROM {$wpdb->prefix}curiero_localities WHERE fan_locality_id IS NOT NULL AND county_initials='%s' AND locality_name='%s' LIMIT 1", $judetdest, $orasdest)
                );

                $extra_km = !empty($localityList) ? $localityList[0]->fan_extra_km : 0;

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
                    if ($tarif_contract !== 'no' && class_exists('CurieRO_Printing_Fan')) {
                        if (WC()->session->get('chosen_payment_method') !== 'cod') {
                            $numele_serviciului = 'Standard';
                            $package_details['codValue'] = 0;
                        } else {
                            $numele_serviciului = ($package_details['codValue'] == 0)
                                ? 'Standard'
                                : 'Cont Colector';
                        }

                        if (get_option('fan_asigurare') === 'da') {
                            $valoare_declarata = $package_details['declaredValue'];
                        } else {
                            $valoare_declarata = 0;
                        }

                        $options = [];
                        if (get_option('fan_deschidere') === 'da') {
                            $options[] = 'A';
                        }

                        if (get_option('fan_sambata') === 'da') {
                            $options[] = 'S';
                        }

                        $numar_colete = get_option('fan_nr_colete');
                        $numar_plicuri = get_option('fan_nr_plicuri');
                        $plata_transportului = get_option('fan_plata_transport');
                        $localitate_dest = $localityList[0]->fan_locality_name;
                        $judet_dest = curiero_get_counties_list($judetdest);

                        $parameters = apply_filters('curiero_overwrite_fan_shipping_parameters', [
                            'info' => [
                                'service' => $numele_serviciului,
                                'payment' => $plata_transportului,
                                'weight' => $package_details['weight'],
                                'options' => $options,
                                'declared_value' => $valoare_declarata,
                                'dimensions' => [
                                    'length' => $package_details['length'],
                                    'width' => $package_details['width'],
                                    'height' => $package_details['height'],
                                ],
                                'packages' => [
                                    'envelope' => (int) $numar_plicuri,
                                    'parcel' => (int) $numar_colete,
                                ],
                            ],
                            'recipient' => [
                                'locality' => $localitate_dest,
                                'county' => $judet_dest,
                            ],
                        ]);

                        $transport = CurieRO()->container->get(APIFanCourierClass::class)->getTarif($parameters);

                        if ($transport == 0) {
                            $transport = $tarif_implicit;
                            $ignoreMethod = ($transport == 0);
                        }

                        if ($tarif_contract === 'estimate_bigger') {
                            $transport = max($transport, $suma_fixa);
                        }

                        if ($transport != 0 && !empty($tarif_contract_tva)) {
                            $transport = $transport + ($transport * $tarif_contract_tva / 100);
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

                $args = apply_filters('curiero_overwrite_fan_shipping', $args, $judetdest, $extra_km, $orasdest);
                $this->add_rate($args);

                if (
                    $this->get_option('collectpoint_activ') === 'yes'
                    && $this->get_collectpoint_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->get_collectpoint_rate($transport, $package, $package_details);
                }

                if (
                    $this->get_option('fanbox_activ') === 'yes'
                    && $this->get_fanbox_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->get_fanbox_rate($transport, $package, $package_details);
                }
            }

            public function get_collectpoint_rate(string $transport, array $package, array $package_details): void
            {
                $tarif_fix_collectpoint = curiero_string_to_float($this->get_option('tarif_collectpoints'));
                if (!empty($tarif_fix_collectpoint)) {
                    $transport = $tarif_fix_collectpoint;
                }

                $prag_gratis_collectpoints = curiero_string_to_float($this->get_option('prag_gratis_collectpoints'));
                if (
                    (!empty($prag_gratis_collectpoints) && $package_details['cartValue'] >= $prag_gratis_collectpoints)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = 'Fan Courier CollectPoint';
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_fan_collectpoint',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_fan_collect_point_shipping', $args);
                $this->add_rate($args);
            }

            public function get_fanbox_rate(string $transport, array $package, array $package_details): void
            {
                if ($package_details['weight'] > 30) {
                    return;
                }

                $tarif_fix_fanbox = $this->get_option('tarif_fanbox');
                if (!empty($tarif_fix_fanbox)) {
                    $transport = $tarif_fix_fanbox;
                }

                $prag_gratis_fanbox = $this->get_option('prag_gratis_fanbox');
                if (
                    (!empty($prag_gratis_fanbox) && $package_details['cartValue'] >= $prag_gratis_fanbox)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = 'Fan Courier FANBox';
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_fan_fanbox',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_fan_box_shipping', $args);
                $this->add_rate($args);
            }

            public function get_fanbox_list(string $county, string $city): LazyCollection
            {
                if ($this->fanbox_list->isNotEmpty()) {
                    return $this->fanbox_list;
                }

                $fanbox_list = collect(
                    CurieRO()->container->get(CurieroFanClass::class)->getFanboxList()
                )->lazy()->remember();

                if ($fanbox_list->isEmpty()) {
                    return $fanbox_list;
                }

                if (strlen($county) <= 2) {
                    $county = curiero_get_counties_list($county);
                }

                $this->fanbox_list = $fanbox_list
                    ->where('county', $county)
                    ->filter(function (array $fanbox) use ($city) {
                        return str_contains($fanbox['locality'] ?? '', $city);
                    })
                    ->sortBy('Name', SORT_NATURAL | SORT_FLAG_CASE);

                if ($this->fanbox_list->isEmpty()) {
                    $this->fanbox_list = $fanbox_list
                        ->where('county', $county)
                        ->sortBy('locality', SORT_NATURAL | SORT_FLAG_CASE);
                }

                return $this->fanbox_list;
            }

            public function get_collectpoint_list(string $county, string $city): LazyCollection
            {
                if ($this->collectpoint_list->isNotEmpty()) {
                    return $this->collectpoint_list;
                }

                $collect_points = collect(
                    CurieRO()->container->get(CurieroFanClass::class)->getCollectPointList()
                )->lazy()->remember();

                if ($collect_points->isEmpty()) {
                    return $collect_points;
                }

                if (strlen($county) <= 2) {
                    $county = curiero_get_counties_list($county);
                }

                $this->collectpoint_list = $collect_points
                    ->where('county', $county)
                    ->where('locality', $city)
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

                if ($this->collectpoint_list->isEmpty()) {
                    $this->collectpoint_list = $collect_points
                        ->where('county', $county)
                        ->sortBy('locality');
                }

                return $this->collectpoint_list;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'FanCourier - Livrare',
        'FanCourier - Livrare',
        curiero_manage_options_capability(),
        'fan_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=fan'));
            exit;
        }
    );
});

// CollectPoint
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('fan', 'collectpoint_activ', 'no')) {
        return;
    }

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);
        $current_collectpoint = $form_data['curiero_fan_collectpoint'] ?? null;
        if ($current_collectpoint && in_array('curiero_fan_collectpoint', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
            $collect_points = $shipping_method->get_collectpoint_list($shipping_county, $shipping_city);

            $current_collectpoint = $collect_points->firstWhere('id', $current_collectpoint);
            WC()->session->set('curiero_fan_selected_collectpoint', $current_collectpoint);
        }

        return $posted_data;
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_fan_collectpoint'])) {
            $current_collectpoint = WC()->session->get('curiero_fan_selected_collectpoint');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_fan_collectpoint', $current_collectpoint['routingLocation']);

            $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
            if ($shipping_method->get_option('collectpoint_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_collectpoint['name'], $current_collectpoint['address']);
            }

            $order->save();
            WC()->session->set('curiero_fan_selected_collectpoint', null);
        }
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_fan_collectpoint = WC()->session->get('curiero_fan_selected_collectpoint');
        if (
            curiero_is_session_shipping_method('curiero_fan_collectpoint')
            && empty($curiero_fan_collectpoint)
        ) {
            wc_add_notice(__('Va rugam sa selectati CollectPoint-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_fan_collectpoint')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
        $collect_points = $shipping_method->get_collectpoint_list($shipping_county, $shipping_city);

        $template_data['collectpoints'] = $collect_points;
        $template_data['selected_collectpoint'] = WC()->session->get('curiero_fan_selected_collectpoint');
        $template_data['current_collectpoint_exists'] = $template_data['collectpoints']->contains($template_data['selected_collectpoint']);

        $notice_message = 'CollectPoint-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_collectpoint_exists'] && !empty($template_data['selected_collectpoint']) && !wc_has_notice($notice_message)) {
            WC()->session->set('curiero_fan_selected_collectpoint', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-collectpoint-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });
});

// FanBox
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('fan', 'fanbox_activ', 'no')) {
        return;
    }

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);
        $current_fanbox_id = $form_data['curiero_fan_fanbox'] ?? null;
        if ($current_fanbox_id && in_array('curiero_fan_fanbox', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
            $fanbox_list = $shipping_method->get_fanbox_list($shipping_county, $shipping_city);

            $current_fanbox = $fanbox_list->firstWhere('id', $current_fanbox_id);
            WC()->session->set('curiero_fan_selected_fanbox', $current_fanbox);
        }

        return $posted_data;
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_fan_fanbox'])) {
            $current_fanbox = WC()->session->get('curiero_fan_selected_fanbox');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_fan_fanbox', $current_fanbox['id']);

            $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
            if ($shipping_method->get_option('fanbox_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_fanbox['name'], $current_fanbox['address']);
            }
            $order->save();
            WC()->session->set('curiero_fan_selected_fanbox', null);
        }
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_fan_fanbox = WC()->session->get('curiero_fan_selected_fanbox');

        if (
            curiero_is_session_shipping_method('curiero_fan_fanbox')
            && empty($curiero_fan_fanbox)
        ) {
            wc_add_notice(__('Va rugam sa selectati FanBox-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_fan_fanbox')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['fan'];
        $fanbox_list = $shipping_method->get_fanbox_list($shipping_county, $shipping_city);

        $template_data['fanbox_list'] = $fanbox_list;
        $template_data['selected_fanbox'] = WC()->session->get('curiero_fan_selected_fanbox');
        $template_data['current_fanbox_exists'] = $template_data['fanbox_list']->contains($template_data['selected_fanbox']);
        $template_data['fanbox_map_active'] = $shipping_method->get_option('fanbox_map_activ');

        $notice_message = 'FanBox-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_fanbox_exists'] && !empty($template_data['selected_fanbox']) && !wc_has_notice($notice_message)) {
            WC()->session->set('curiero_fan_selected_fanbox', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-fanbox-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });

    if (curiero_shipping_option_value_is('fan', 'fanbox_map_activ', 'no')) {
        return;
    }

    add_action('wp_enqueue_scripts', function (): void {
        if (is_checkout()) {
            wp_enqueue_script('map-fanbox-points', 'https://unpkg.com/map-fanbox-points@latest/umd/map-fanbox-points.js');

            wp_enqueue_script(
                'fanbox-map',
                plugin_dir_url(__FILE__) . 'assets/js/fanbox-map.js',
                ['jquery', 'map-fanbox-points'],
                '1.0.0',
                false
            );
        }
    });
});

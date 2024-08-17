<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function Sameday_Shipping_Method(): void
{
    if (!class_exists('Sameday_Shipping_Method')) {
        class Sameday_Shipping_Method extends WC_Shipping_Method
        {
            public $easybox_list;

            public function __construct()
            {
                $this->id = 'sameday';
                $this->method_title = __('Sameday Shipping', 'sameday');
                $this->method_description = __('Sameday Shipping Method for courier', 'sameday');

                $this->availability = 'including';
                $this->countries = ['RO'];

                $this->init();

                $this->title = $this->get_option('title');

                $this->easybox_list = collect()->lazy();
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
                        'default' => __('Sameday', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['required' => 'required'],
                    ],
                    'tarif_contract' => [
                        'title' => __('Afisare tarif contract', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'description' => __('Pentru a activa aceasta optiune trebuie sa aveti si metoda Sameday - AWB activata si configurata.', 'curiero-plugin'),
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
                        'description' => __('Tariful final nu poate depasi aceasta valoare.', 'curiero-plugin'),
                    ],
                    'lockers_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Activeaza serviciul EasyBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege optiunea EasyBox Sameday. Nu veti putea genera AWB daca nu aveti cont premium activ.', 'curiero-plugin'),
                    ],
                    'tarif_lockers' => [
                        'title' => __('<span style="color:red">[Premium]</span> Suma fixa pentru EasyBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Daca doriti ca pretul sa fie completat automat cu tariful de contract, lasati acesti camp gol sau 0.', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'prag_gratis_lockers' => [
                        'title' => __('<span style="color:red">[Premium]</span> Prag gratis EasyBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('250', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'lockers_map_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Activeaza Harta EasyBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege un EasyBox Sameday prin intermediul hartii. Copyright 2022 Sameday', 'curiero-plugin'),
                    ],
                    'locker_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span>  Modifica adresa de livrare cu adresa Easybox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica Easybox-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - Sameday Livrare: Ghid de completare a setărilor de livrare.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare Sameday {$help_tip}</h2>
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

                $tarif_maxim = curiero_string_to_float($this->get_option('tarif_maxim') ?: 99999);
                $tarif_implicit = curiero_string_to_float($this->get_option('tarif_implicit'));
                $ignoreMethod = false;

                $localityList = $wpdb->get_results(
                    $wpdb->prepare("SELECT sameday_extra_km FROM {$wpdb->prefix}curiero_localities WHERE sameday_locality_id IS NOT NULL AND county_initials='%s' AND locality_name='%s' LIMIT 1", $judetdest, $orasdest)
                );

                $extra_km = !empty($localityList) ? $localityList[0]->sameday_extra_km : 0;

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
                    if (($tarif_contract !== 'no') && class_exists('CurieRO_Printing_Sameday')) {
                        if (WC()->session->get('chosen_payment_method') !== 'cod') {
                            $package_details['codValue'] = 0;
                        }

                        try {
                            $parameters = apply_filters('curiero_shipping_calculate_contract', [
                                'weight' => $package_details['weight'],
                                'length' => $package_details['length'],
                                'width' => $package_details['width'],
                                'height' => $package_details['height'],
                                'city' => $orasdest,
                                'state' => curiero_get_counties_list($judetdest),
                                'address' => $package['destination']['address'],
                                'declared_value' => get_option('sameday_declared_value') ?: 0,
                                'cod_value' => $package_details['codValue'],
                                'service_id' => get_option('sameday_ord_service_id'),
                            ], 'Sameday');

                            $transport = CurieRO()->container->get(APISamedayClass::class)->calculate($parameters);
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

                $args = apply_filters('curiero_overwrite_sameday_shipping', $args, $judetdest, $orasdest, $extra_km);
                $this->add_rate($args);

                if (
                    $this->get_option('lockers_activ') === 'yes'
                    && $this->get_easybox_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->get_easybox_rate($package, $package_details);
                }
            }

            public function get_easybox_rate(array $package, array $package_details): void
            {
                if (
                    $package_details['width'] > 47
                    || $package_details['length'] > 47
                    || $package_details['height'] > 47
                    || $package_details['weight'] > 20
                ) {
                    return;
                }

                $judetdest = $package['destination']['state'] ?? '';
                $orasdest = $package['destination']['city'] ?? '';

                $package_details = curiero_calculate_package_details($this, $package);
                if (empty($package_details)) {
                    return;
                }

                $tarif_contract = $this->get_option('tarif_contract');

                $prag_gratis = curiero_string_to_float($this->get_option('prag_gratis_lockers'));
                $tarif_maxim = curiero_string_to_float($this->get_option('tarif_maxim') ?: 99999);
                $tarif_lockers = curiero_string_to_float($this->get_option('tarif_lockers'));
                $ignoreMethod = false;

                if (
                    $package_details['cartValue'] >= $prag_gratis
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                } elseif (
                    empty($orasdest)
                ) {
                    $transport = $tarif_lockers;
                    $ignoreMethod = ($transport == 0);
                } else {
                    if (($tarif_contract !== 'no') && class_exists('CurieRO_Printing_Sameday')) {
                        try {
                            $parameters = apply_filters('curiero_shipping_calculate_contract', [
                                'weight' => $package_details['weight'],
                                'length' => $package_details['length'],
                                'width' => $package_details['width'],
                                'height' => $package_details['height'],
                                'city' => $orasdest,
                                'state' => curiero_get_counties_list($judetdest),
                                'address' => $package['destination']['address'],
                                'declared_value' => get_option('sameday_declared_value') ?: 0,
                                'cod_value' => $package_details['codValue'],
                                'service_id' => get_option('sameday_locker_service_id'),
                            ], 'Sameday');

                            $transport = CurieRO()->container->get(APISamedayClass::class)->calculate($parameters);
                        } catch (Exception $e) {
                            $transport = $tarif_lockers;
                            $ignoreMethod = ($transport == 0);
                        }

                        if ($tarif_contract === 'estimate_bigger') {
                            $transport = max($transport, $tarif_lockers);
                        }
                    } else {
                        $transport = $tarif_lockers;
                    }
                }

                if ($ignoreMethod || !is_numeric($transport)) {
                    return;
                }

                $transport = min($transport, $tarif_maxim);

                $label = 'Sameday EasyBox';
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_sameday_lockers',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_sameday_easybox_shipping', $args, $judetdest, $orasdest);
                $this->add_rate($args);
            }

            public function get_easybox_list(string $county, string $city): LazyCollection
            {
                if ($this->easybox_list->isNotEmpty()) {
                    return $this->easybox_list;
                }

                $easybox_list = collect(
                    CurieRO()->container->get(CurieroSamedayClass::class)->getLockers()
                )->lazy()->remember();

                if ($easybox_list->isEmpty()) {
                    return $easybox_list;
                }

                if (strlen($county) <= 2) {
                    $county = curiero_get_counties_list($county);
                }

                $this->easybox_list = $easybox_list
                    ->where('county', $county)
                    ->where('city', $city)
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

                if ($this->easybox_list->isEmpty()) {
                    $this->easybox_list = $easybox_list
                        ->where('county', $county)
                        ->sortBy('city', SORT_NATURAL | SORT_FLAG_CASE);
                }

                return $this->easybox_list;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'Sameday - Livrare',
        'Sameday - Livrare',
        curiero_manage_options_capability(),
        'sameday_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=sameday'));
            exit;
        }
    );
});

// SameDay - Lockers
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('sameday', 'lockers_activ', 'no')) {
        return;
    }

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);
        $current_locker_id = $form_data['curiero_sameday_lockers'] ?? null;
        if ($current_locker_id && in_array('curiero_sameday_lockers', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['sameday'];
            $lockers = $shipping_method->get_easybox_list($shipping_county, $shipping_city);

            $current_locker = $lockers->firstWhere('id', $current_locker_id);
            WC()->session->set('curiero_sameday_selected_locker', $current_locker);
        }

        return $posted_data;
    });

    add_filter('woocommerce_available_payment_gateways', function (array $available_gateways): array {
        if ($session = WC()->session) {
            $chosen_shipping_methods = $session->get('chosen_shipping_methods');
            $current_locker = $session->get('curiero_sameday_selected_locker');
            if ($current_locker) {
                $supportedPayment = $current_locker['supportedPayment'] ?? 0;
                if (in_array('curiero_sameday_lockers', $chosen_shipping_methods) && $supportedPayment == 0) {
                    unset($available_gateways['cod']);
                    $message = 'La Easybox-ul selectat nu se pot face plati ramburs (nu se poate plati cu cardul la ridicarea comenzii).';
                    if (!wc_has_notice($message, 'notice')) {
                        wc_add_notice($message, 'notice');
                    }
                }
            }
        }

        return $available_gateways;
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_sameday_lockers'])) {
            $current_locker = WC()->session->get('curiero_sameday_selected_locker');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_sameday_lockers', $current_locker['id']);
            $order->update_meta_data('curiero_sameday_locker_name', $current_locker['name']);

            $shipping_method = WC()->shipping->get_shipping_methods()['sameday'];
            if ($shipping_method && $shipping_method->get_option('locker_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_locker['name'], $current_locker['address']);
            }

            $order->save();
            WC()->session->set('curiero_sameday_selected_locker', null);
        }
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_sameday_lockers = WC()->session->get('curiero_sameday_selected_locker');

        if (
            curiero_is_session_shipping_method('curiero_sameday_lockers')
            && empty($curiero_sameday_lockers)
        ) {
            wc_add_notice(__('Va rugam sa selectati Easybox-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_sameday_lockers')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['sameday'];
        $lockers = $shipping_method->get_easybox_list($shipping_county, $shipping_city);

        $template_data['lockers'] = apply_filters('curiero_easybox_list_filter', $lockers);
        $template_data['local_box_found'] = $lockers->isNotEmpty() ? ($lockers->first()['city'] ?? '') === $shipping_city : false;
        $template_data['current_locker'] = WC()->session->get('curiero_sameday_selected_locker');
        $template_data['current_locker_exists'] = $template_data['lockers']->contains($template_data['current_locker']);
        $template_data['lockers_map_active'] = $shipping_method->get_option('lockers_map_activ');

        $notice_message = 'Easybox-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_locker_exists'] && !empty($template_data['current_locker']) && !wc_has_notice($notice_message, 'error')) {
            WC()->session->set('curiero_sameday_selected_locker', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-lockers-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });

    if (curiero_shipping_option_value_is('sameday', 'lockers_map_activ', 'no')) {
        return;
    }

    add_action('wp_enqueue_scripts', function (): void {
        if (is_checkout()) {
            wp_enqueue_script('preprod-locker-plugin', 'https://cdn.sameday.ro/locker-plugin/lockerpluginsdk.js');
            wp_enqueue_script('lockers_script', plugin_dir_url(__FILE__) . 'assets/js/lockers_map.js', ['jquery', 'preprod-locker-plugin'], '1.0.3');
        }
    });
});

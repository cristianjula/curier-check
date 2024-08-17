<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function DPD_Shipping_Method(): void
{
    if (!class_exists('DPD_Shipping_Method')) {
        class DPD_Shipping_Method extends WC_Shipping_Method
        {
            public $dpdbox_list;

            public function __construct()
            {
                $this->id = 'dpd';
                $this->method_title = __('DPD Shipping', 'dpd');
                $this->method_description = __('DPD Shipping Method for courier', 'dpd');

                $this->availability = 'including';
                $this->countries = [
                    'RO','HU','BG','GR','PL','CZ',
                    'DE','FR','NL','AT','BE','DK','EE','FI','IT','LV','LT','LU','PT','ES','SE'
                ];

                $this->init();

                $this->title = $this->get_option('title');

                $this->dpdbox_list = collect()->lazy();
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
                        'default' => __('DPD', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['required' => 'required'],
                    ],
                    'tarif_contract' => [
                        'title' => __('Afisare tarif contract', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'no',
                        'css' => 'width:400px;',
                        'description' => __('Pentru a activa aceasta optiune trebuie sa aveti si metoda DPD - AWB activata si configurata.', 'curiero-plugin'),
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
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'description' => __('Tariful implicit pentru metoda de livrare, pentru a arata o suma atunci cand nu este introdusa adresa de livrare.', 'curiero-plugin'),
                    ],
                    'tarif_maxim' => [
                        'title' => __('Tarif maxim livrare', 'curiero-plugin'),
                        'type' => 'number',
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('40', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Tariful final nu poate depasi aceasta valoare.', 'curiero-plugin'),
                    ],
                    'dpd_box' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza serviciul DPDBox ', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'fan'),
                            'yes' => __('Da', 'fan'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege livrarea catre un Collect Point DPD.', 'curiero-plugin'),
                    ],
                    'tarif_dpdbox' => [
                        'title' => __('<span style="color:red">[Premium]</span> Suma fixa pentru DPDBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'prag_gratis_dpdbox' => [
                        'title' => __('<span style="color:red">[Premium]</span> Prag gratis DPDBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('250', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'dpdbox_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span> Modifica adresa de livrare cu adresa DPDBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica DPDBox-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - DPD Livrare: Ghid de completare a setărilor de livrare.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare DPD {$help_tip}</h2>
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
                $judetdest = $package['destination']['state'] ?? '';
                $orasdest = $package['destination']['city'] ?? '';
                $postcode = $package['destination']['postcode'] ?? '';
                $codtara = curiero_remove_accents($package['destination']['country']);
                $taradest = CurieRO()->container->get(APIDPDClass::class)->supported_countries[$codtara] ?? '';

                if (empty($taradest)) {
                    return;
                }

                $package_details = curiero_calculate_package_details($this, $package);
                if (empty($package_details)) {
                    return;
                }

                $tarif_contract = $this->get_option('tarif_contract');

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

                if (
                    $package_details['cartValue'] >= $prag_gratis
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                } elseif (
                    empty($orasdest)
                ) {
                    $transport = $tarif_implicit;
                    $ignoreMethod = ($transport == 0);
                } else {
                    if (($tarif_contract !== 'no') && class_exists('CurieRO_Printing_DPD')) {
                        if (WC()->session->get('chosen_payment_method') !== 'cod') {
                            $package_details['codValue'] = 0;
                        }

                        $zip = curiero_get_post_code($judetdest, $orasdest, $postcode, $package['destination']['country']);
                        $service_ids = ($taradest['name'] === "Romania") ? get_option('dpd_service_id') : get_option('dpd_international_service_id');

                        $req_vars = apply_filters('curiero_shipping_calculate_contract', [
                            'sender_id' => get_option('dpd_sender_id'),
                            'service_ids' => $service_ids,
                            'recipient_private_person' => 'y',
                            'courier_service_payer' => get_option('dpd_courier_service_payer') ?: 'SENDER',
                            'third_party_client_id' => get_option('dpd_sender_id'),
                            'parcels_count' => get_option('dpd_parcel_count') ?: 1,
                            'total_weight' => $package_details['weight'],
                            'recipient_address_site_name' => $orasdest,
                            'recipient_address_state_id' => ($taradest['name'] === "Romania") ? $judetdest : '',
                            'recipient_address_postcode' => $zip,
                            'autoadjust_pickup_date' => 'y',
                            'cod_amount' => ($taradest['name'] === "Romania") ? $package_details['codValue'] : '',
                            'cod_currency' => get_woocommerce_currency(),
                            'recipient_address_country_id' => $taradest['numeric_iso'],
                        ], 'DPD');

                        try {
                            $dpd_client = CurieRO()->container->get(APIDPDClass::class);
                            $transport = $dpd_client->calculate($req_vars);
                         } catch (Exception $e) {
                            $transport = $tarif_implicit;
                            $ignoreMethod = ($transport == 0);
                        }

                        if ($tarif_contract === 'estimate_bigger') {
                            $transport = max($transport, $suma_fixa);
                        }
                    } else {
                        $transport = curiero_calculate_self_shipping_costs($this, $package_details);
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

                $args = apply_filters('curiero_overwrite_dpd_shipping', $args, $judetdest, $orasdest);
                $this->add_rate($args);

                if (
                    $this->get_option('dpd_box') === 'yes'
                    && $this->get_dpdbox_list($orasdest)->isNotEmpty()
                ) {
                    $this->get_dpdbox_rate($transport, $package, $package_details);
                }
            }

            public function get_dpdbox_rate(string $transport, array $package, array $package_details): void
            {
                $tarif_dpdbox = curiero_string_to_float($this->get_option('tarif_dpdbox'));
                if (!empty($tarif_dpdbox)) {
                    $transport = $tarif_dpdbox;
                }

                $prag_gratis_dpdbox = $this->get_option('prag_gratis_dpdbox');
                if (
                    (!empty($prag_gratis_dpdbox) && $package_details['cartValue'] >= $prag_gratis_dpdbox)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = 'DPDBox';
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_dpd_box',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_dpd_box_shipping', $args);
                $this->add_rate($args);
            }

            public function get_dpdbox_list(string $city): LazyCollection
            {
                if ($this->dpdbox_list->isNotEmpty()) {
                    return $this->dpdbox_list;
                }

                $dpdbox_list = collect(
                    CurieRO()->container->get(CurieroDPDClass::class)->getDPDboxes()
                )->lazy()->remember();

                if ($dpdbox_list->isEmpty()) {
                    return $dpdbox_list;
                }

                if (str_contains($city, 'Sector ')) {
                    $city = 'Bucuresti';
                }

                $this->dpdbox_list = $dpdbox_list
                    ->where('city', $city)
                    ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);

                return $this->dpdbox_list;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'DPD - Livrare',
        'DPD - Livrare',
        curiero_manage_options_capability(),
        'dpd_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=dpd'));
            exit;
        }
    );
});

// DPDBox
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('dpd', 'dpd_box', 'no')) {
        return;
    }

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_dpd_box'])) {
            $current_dpd_box = WC()->session->get('curiero_dpd_selected_box');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_dpd_box', $current_dpd_box['id']);
            $order->update_meta_data('curiero_dpd_box_address', $current_dpd_box['address']);

            $shipping_method = WC()->shipping->get_shipping_methods()['dpd'];
            if ($shipping_method->get_option('dpdbox_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, 'dpdbox ' . $current_dpd_box['name'], $current_dpd_box['address']);
            }

            $order->save();
            WC()->session->set('curiero_dpd_selected_box', null);
        }
    });

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);

        $current_dpd_box = $form_data['curiero_dpd_box'] ?? null;
        if ($current_dpd_box && in_array('curiero_dpd_box', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];

            $shipping_method = WC()->shipping->get_shipping_methods()['dpd'];
            $dpd_boxes = $shipping_method->get_dpdbox_list($shipping_city);

            $current_dpd_box = $dpd_boxes->where('id', $current_dpd_box)->first();
            WC()->session->set('curiero_dpd_selected_box', (array) $current_dpd_box);
        }

        return $posted_data;
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_dpd_box = WC()->session->get('curiero_dpd_selected_box');

        if (
            curiero_is_session_shipping_method('curiero_dpd_box')
            && empty($curiero_dpd_box)
        ) {
            wc_add_notice(__('Va rugam sa selectati DPDBox-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_dpd_box')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];

        $shipping_method = WC()->shipping->get_shipping_methods()['dpd'];
        $dpd_boxes = $shipping_method->get_dpdbox_list($shipping_city);

        $template_data['dpd_boxes'] = $dpd_boxes;
        $template_data['current_dpd_box'] = WC()->session->get('curiero_dpd_selected_box');
        $template_data['current_box_exists'] = $template_data['dpd_boxes']->contains($template_data['current_dpd_box']);

        $notice_message = 'DPDBox-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_box_exists'] && !empty($template_data['current_dpd_box']) && !wc_has_notice($notice_message)) {
            WC()->session->set('curiero_dpd_selected_box', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-dpd-boxes-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });
});

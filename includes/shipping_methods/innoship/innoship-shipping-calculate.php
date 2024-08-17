<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function Innoship_Shipping_Method(): void
{
    if (!class_exists('Innoship_Shipping_Method')) {
        class Innoship_Shipping_Method extends WC_Shipping_Method
        {
            public $locker_list;

            public function __construct()
            {
                $this->id = 'innoship';
                $this->method_title = __('Innoship Shipping', 'innoship');
                $this->method_description = __('Innoship Shipping Method for courier', 'innoship');

                $this->availability = 'including';
                $this->countries = ['RO', 'IT'];

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
                        'title' => __('Denumire metoda livrare', 'innoship'),
                        'type' => 'text',
                        'description' => __('Denumirea metodei de livrare in Cart si Checkout, vizibila de catre client.', 'innoship'),
                        'default' => __('Innoship', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['required' => 'required'],
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
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                        'default' => __('0', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Tariful implicit pentru metoda de livrare, pentru a arata o suma atunci cand nu este introdusa adresa de livrare. ', 'curiero-plugin'),
                    ],
                    'lockers_activ' => [
                        'title' => __('Activeaza serviciul Lockers', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege livrarea catre un Locker.', 'curiero-plugin'),
                    ],
                    'tarif_lockers' => [
                        'title' => __('Suma fixa pentru Lockers', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'prag_gratis_lockers' => [
                        'title' => __('Prag gratis Locker', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('250', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'lockers_shipping_address' => [
                        'title' => __('Modifica adresa de livrare cu adresa Locker', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica Locker-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - Innoship Livrare: Ghid de completare a setărilor de calcul cost in checkout.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare Innoship {$help_tip}</h2>
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

                $package_details = curiero_calculate_package_details($this, $package);
                if (empty($package_details)) {
                    return;
                }

                if ($judetdest === 'B') {
                    $prag_gratis = curiero_string_to_float($this->get_option('prag_gratis_Bucuresti'));
                } else {
                    $prag_gratis = curiero_string_to_float($this->get_option('prag_gratis_provincie'));
                }

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
                    $transport = curiero_calculate_self_shipping_costs($this, $package_details);
                }

                if ($ignoreMethod || !is_numeric($transport)) {
                    return;
                }

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

                $args = apply_filters('curiero_overwrite_innoship_shipping', $args, $judetdest, $orasdest);

                $this->add_rate($args);

                if (
                    $this->get_option('lockers_activ') === 'yes'
                    && $this->get_locker_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->get_locker_rate($transport, $package, $package_details);
                }
            }

            public function get_locker_rate(string $transport, array $package, array $package_details): void
            {
                $tarif_lockers = curiero_string_to_float($this->get_option('tarif_lockers'));
                if (!empty($tarif_lockers)) {
                    $transport = $tarif_lockers;
                }

                $prag_gratis_lockers = $this->get_option('prag_gratis_lockers');
                if (
                    (!empty($prag_gratis_lockers) && $package_details['cartValue'] >= $prag_gratis_lockers)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = "{$this->title} Locker";
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_innoship_locker',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_innoship_lockers_shipping', $args);
                $this->add_rate($args);
            }

            public function get_locker_list(string $county, string $city): LazyCollection
            {
                if ($this->locker_list->isNotEmpty()) {
                    return $this->locker_list;
                }

                if (str_contains($city, 'Sector')) {
                    $city = 'Bucuresti';
                }

                $this->locker_list = collect(
                    CurieRO()->container->get(APIInnoshipClass::class)->getLockers($county, $city)
                )->lazy()->remember();

                $this->locker_list = $this->locker_list->unique('id')->sortBy('name');

                return $this->locker_list;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'Innoship - Livrare',
        'Innoship - Livrare',
        curiero_manage_options_capability(),
        'innoship_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=innoship'));
            exit;
        }
    );
});

// Lockers
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('innoship', 'lockers_activ', 'no')) {
        return;
    }

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);

        $current_locker_id = $form_data['curiero_innoship_locker'] ?? null;
        if ($current_locker_id && in_array('curiero_innoship_locker', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['innoship'];
            $locker_list = $shipping_method->get_locker_list($shipping_county, $shipping_city);

            $current_locker = $locker_list->firstWhere('id', $current_locker_id);
            WC()->session->set('curiero_selected_innoship_locker', $current_locker);
        }

        return $posted_data;
    });

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_innoship_locker'])) {
            $current_locker = WC()->session->get('curiero_selected_innoship_locker');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_innoship_locker', $current_locker['id']);

            $shipping_method = WC()->shipping->get_shipping_methods()['innoship'];
            if ($shipping_method->get_option('lockers_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_locker['name'], $current_locker['addressText']);
            }

            $order->save();
            WC()->session->set('curiero_selected_innoship_locker', null);
        }
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_innoship_locker = WC()->session->get('curiero_selected_innoship_locker');

        if (
            curiero_is_session_shipping_method('curiero_innoship_locker')
            && empty($curiero_innoship_locker)
        ) {
            wc_add_notice(__('Va rugam sa selectati Locker-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_innoship_locker')) {
            return;
        }

        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['innoship'];
        $locker_list = $shipping_method->get_locker_list($shipping_county, $shipping_city);

        $template_data['locker_list'] = $locker_list;
        $template_data['selected_locker'] = WC()->session->get('curiero_selected_innoship_locker');
        $template_data['selected_locker_exists'] = collect($template_data['locker_list'])->contains($template_data['selected_locker']);

        $notice_message = 'Locker-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['selected_locker_exists'] && !empty($template_data['selected_locker']) && !wc_has_notice($notice_message)) {
            WC()->session->set('curiero_selected_innoship_locker', null);
            wc_add_notice($notice_message, 'error');
        }

        wc_get_template('templates/checkout-locker-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });
});

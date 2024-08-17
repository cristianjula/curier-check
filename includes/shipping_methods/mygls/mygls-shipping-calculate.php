<?php

use CurieRO\Illuminate\Support\LazyCollection;

// Exit if accessed directly
defined('ABSPATH') || exit;

function MyGLS_Shipping_Method(): void
{
    if (!class_exists('MyGLS_Shipping_Method')) {
        class MyGLS_Shipping_Method extends WC_Shipping_Method
        {
            public $mygls_box_list;

            public function __construct()
            {
                $this->id = 'mygls';
                $this->method_title = __('MyGLS Shipping', 'mygls');
                $this->method_description = __('MyGLS Shipping Method for courier', 'mygls');

                $this->availability = 'including';
                $this->countries = ['RO'];

                $this->init();

                $this->title = $this->get_option('title');

                $this->mygls_box_list = collect()->lazy();
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
                        'default' => __('GLS', 'curiero-plugin'),
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
                    'mygls_box_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza serviciul MyGLSBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege livrarea catre un Collect Point MYGLS. Setarea va fi ignorata daca folositi un cont CurieRO FanCourier gratuit.', 'curiero-plugin'),
                    ],
                    'tarif_mygls_box' => [
                        'title' => __('<span style="color:red">[Premium]</span> Suma fixa pentru MyGLSBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => __('15', 'curiero-plugin'),
                        'desc_tip' => true,
                        'description' => __('Daca doriti ca pretul sa fie completat automat cu tariful de contract, lasati acesti camp gol sau 0.', 'curiero-plugin'),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'box_map_activ' => [
                        'title' => __('<span style="color:red">[Premium]</span> Activeaza Harta MyGLS Box', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Pe pagina de checkout clientii vor avea posibilitatea de a alege un MyGLS Box prin intermediul hartii.', 'curiero-plugin'),
                    ],
                    'prag_gratis_mygls_box' => [
                        'title' => __('<span style="color:red">[Premium]</span> Prag gratis MyGLSBox', 'curiero-plugin'),
                        'type' => 'number',
                        'default' => $this->get_option('prag_gratis_mygls', __('250', 'curiero-plugin')),
                        'custom_attributes' => ['step' => 'any', 'min' => '0'],
                    ],
                    'mygls_box_shipping_address' => [
                        'title' => __('<span style="color:red">[Premium]</span> Modifica adresa de livrare cu adresa MyGLSBox', 'curiero-plugin'),
                        'type' => 'select',
                        'default' => 'Nu',
                        'css' => 'width:400px;',
                        'options' => [
                            'no' => __('Nu', 'curiero-plugin'),
                            'yes' => __('Da', 'curiero-plugin'),
                        ],
                        'desc_tip' => true,
                        'description' => __('Adresa de livrare va fi suprascrisa cu adresa specifica MyGLSBox-ului ales in pagina de checkout.', 'curiero-plugin'),
                    ],
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - MyGLS Livrare: Ghid de completare a setărilor de livrare.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare MyGLS {$help_tip}</h2>
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

                $args = apply_filters('curiero_overwrite_mygls_shipping', $args, $judetdest, $orasdest);
                $this->add_rate($args);

                if (
                    $this->get_option('mygls_box_activ') === 'yes'
                    && $this->get_mygls_box_list($judetdest, $orasdest)->isNotEmpty()
                ) {
                    $this->get_mygls_rate($transport, $package, $package_details);
                }
            }

            public function get_mygls_rate(string $transport, array $package, array $package_details): void
            {
                $tarif_fix_mygls_box = $this->get_option('tarif_mygls_box');
                if (!empty($tarif_fix_mygls_box)) {
                    $transport = $tarif_fix_mygls_box;
                }

                $prag_gratis_mygls_box = $this->get_option('prag_gratis_mygls_box');
                
                if (
                    (!empty($prag_gratis_mygls_box) && $package_details['cartValue'] >= $prag_gratis_mygls_box)
                    || curiero_cart_has_free_shipping_coupon()
                ) {
                    $transport = 0;
                }

                $label = 'GLS Box';
                if ($transport == 0) {
                    $label .= ': Gratuit';
                }

                $args = [
                    'id' => 'curiero_mygls_box',
                    'label' => $label,
                    'cost' => $transport,
                    'taxes' => true,
                ];

                $args = apply_filters('curiero_overwrite_mygls_box_shipping', $args);
                $this->add_rate($args);
            }

            public function get_mygls_box_list(string $county, string $city): LazyCollection
            {
                if ($this->mygls_box_list->isNotEmpty()) {
                    return $this->mygls_box_list;
                }

                $mygls_box_list = collect(
                    CurieRO()->container->get(CurieroMyGLSClass::class)->getLockers()
                )->lazy()->remember();

                $this->mygls_box_list = $mygls_box_list
                    ->where('county', $county)
                    ->where('city', $city);

                if ($this->mygls_box_list->isEmpty()) {
                    $this->mygls_box_list = $mygls_box_list
                        ->where('county', $county)
                        ->where('city', $city . ' ' . curiero_get_counties_list($county))
                        ->sortBy('city');
                }

                if ($this->mygls_box_list->isEmpty()) {
                    $this->mygls_box_list = $mygls_box_list
                        ->where('county', $county)
                        ->sortBy('city', SORT_NATURAL | SORT_FLAG_CASE);
                }

                return $this->mygls_box_list;
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'MyGLS - Livrare',
        'MyGLS - Livrare',
        curiero_manage_options_capability(),
        'mygls_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=mygls'));
            exit;
        }
    );
});

// MYGLSBox
add_action('curiero_loaded', function (): void {
    if (curiero_shipping_option_value_is('mygls', 'mygls_box_activ', 'no')) {
        return;
    }

    add_action('woocommerce_checkout_update_order_meta', function (int $order_id): void {
        if (!empty($_POST['curiero_mygls_box'])) {
            $current_mygls_box = WC()->session->get('curiero_selected_mygls_box');
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_mygls_box', $current_mygls_box['id']);

            $shipping_method = WC()->shipping->get_shipping_methods()['mygls'];
            if ($shipping_method->get_option('mygls_box_shipping_address') === 'yes') {
                curiero_force_locker_shipping_address($order, $current_mygls_box['name'], $current_mygls_box['address']);
            }
            $order->save();
            WC()->session->set('curiero_selected_mygls_box', null);
        }
    });

    add_filter('woocommerce_checkout_update_order_review', function (string $posted_data): string {
        $form_data = [];
        parse_str($posted_data, $form_data);
        $current_mygls_box = $form_data['curiero_mygls_box'] ?? null;

        if ($current_mygls_box && in_array('curiero_mygls_box', $form_data['shipping_method'] ?? [])) {
            $shipping_city = WC()->session->get('customer')['shipping_city'];
            $shipping_county = WC()->session->get('customer')['shipping_state'];

            $shipping_method = WC()->shipping->get_shipping_methods()['mygls'];
            $mygls_box_list = $shipping_method->get_mygls_box_list($shipping_county, $shipping_city);
            $current_mygls_box = $mygls_box_list->firstWhere('id', $current_mygls_box);
            WC()->session->set('curiero_selected_mygls_box', $current_mygls_box);
        }

        return $posted_data;
    });

    add_action('woocommerce_checkout_process', function (): void {
        $curiero_mygls_box = WC()->session->get('curiero_selected_mygls_box');
        if (
            curiero_is_session_shipping_method('curiero_mygls_box')
            && empty($curiero_mygls_box)
        ) {
            wc_add_notice(__('Va rugam sa selectati MyGLSBox-ul dorit.'), 'error');
        }
    });

    add_action('woocommerce_review_order_after_shipping', function (): void {
        if (!curiero_is_session_shipping_method('curiero_mygls_box')) {
            return;
        }
        $shipping_city = WC()->session->get('customer')['shipping_city'];
        $shipping_county = WC()->session->get('customer')['shipping_state'];

        $shipping_method = WC()->shipping->get_shipping_methods()['mygls'];
        $mygls_box_list = $shipping_method->get_mygls_box_list($shipping_county, $shipping_city);

        $template_data['mygls_box_list'] = $mygls_box_list;
        $template_data['selected_mygls_box'] = WC()->session->get('curiero_selected_mygls_box');
        $template_data['current_mygls_box_exists'] = $template_data['mygls_box_list']->contains($template_data['selected_mygls_box']);
        $template_data['box_map_active'] = $shipping_method->get_option('box_map_activ');

        $notice_message = 'MyGLSBox-ul selectat precedent nu mai este valabil. Va rugam sa alegeti alt punct de livrare.';
        if (!$template_data['current_mygls_box_exists'] && !empty($template_data['selected_mygls_box']) && !wc_has_notice($notice_message)) {
            WC()->session->set('curiero_selected_mygls_box', null);
            wc_add_notice($notice_message, 'error');
        }
        wc_get_template('templates/checkout-myglsbox-select.php', $template_data, '', plugin_dir_path(__FILE__));
    });

    if (curiero_shipping_option_value_is('mygls', 'box_map_activ', 'no')) {
        return;
    }

    add_action('wp_footer', function (): void {
        echo '<gls-dpm-dialog country="ro" class="inchoo-gls-map gls-map-locker" filter-type="parcel-locker" id="mygls-map"></gls-dpm-dialog>';
    });

    add_filter('script_loader_tag', function (string $tag, string $handle, string $src): string {
        if ('gls-shipping-dpm' === $handle) {
            $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
        }

        return $tag;
    }, 10, 3);

    add_action('wp_enqueue_scripts', function (): void {
        if (is_checkout()) {
            $plugin_dir_path = plugin_dir_url(__FILE__);

            // Enqueue the second JS file
            wp_enqueue_script(
                'gls-shipping-dpm',
                $plugin_dir_path . 'assets/js/gls-dpm.js',
                [],
                '1.0.0',
                false
            );
            // Enqueue the first JS file
            wp_enqueue_script(
                'gls-shipping-public',
                $plugin_dir_path . 'assets/js/gls-shipping-public.js',
                ['jquery', 'gls-shipping-dpm'],
                '1.0.0',
                false
            );
        }
    });
});

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

function Optimus_Shipping_Method(): void
{
    if (!class_exists('Optimus_Shipping_Method')) {
        class Optimus_Shipping_Method extends WC_Shipping_Method
        {
            public function __construct()
            {
                $this->id = 'optimus';
                $this->method_title = __('OptimusCourier Shipping', 'optimus');
                $this->method_description = __('OptimusCourier Shipping Method for courier', 'optimus');

                $this->availability = 'including';
                $this->countries = ['RO'];

                $this->init();

                $this->title = $this->get_option('title');
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
                        'title' => __('Denumire metoda livrare *', 'curiero-plugin'),
                        'type' => 'text',
                        'description' => __('Denumirea metodei de livrare in Cart si Checkout, vizibila de catre client.', 'curiero-plugin'),
                        'default' => __('OptimusCourier', 'curiero-plugin'),
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
                        'title' => __('Prag kilograme gratuite', 'curiero-plugin'),
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
                ];
            }

            public function admin_options(): void
            {
                $help_tip = wc_help_tip('In cazul in care intampinati probleme la configurare va rugam sa verificati documentatia CurieRO - Optimus Courier Livrare: Ghid de completare a setărilor de calcul cost in checkout.');
                echo <<<HTML
                    <style>table.form-table th{padding-left:1.5rem!important}table.form-table td{padding-right:1.5rem!important}table.form-table select,table.form-table input{width:100%!important}</style>
                    <h2>CurieRO - Metoda de livrare Optimus Courier {$help_tip}</h2>
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

                $args = apply_filters('curiero_overwrite_optimus_shipping', $args, $judetdest, $orasdest);
                $this->add_rate($args);
            }
        }
    }
}

add_action('admin_menu', function (): void {
    add_submenu_page(
        'curiero-menu-content',
        'Optimus - Livrare',
        'Optimus - Livrare',
        curiero_manage_options_capability(),
        'optimus_redirect',
        function (): void {
            wp_safe_redirect(curiero_build_url('admin.php?page=wc-settings&tab=shipping&section=optimus'));
            exit;
        }
    );
});

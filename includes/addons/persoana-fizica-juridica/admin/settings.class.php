<?php

class CurieRO_PFPJ_Admin_Settings
{
    protected $defaults;

    public function __construct()
    {
        $this->defaults = [
            'curiero_pfiz_label' => esc_html__('Persoana Fizica', 'curiero_pf_pj'),
            'curiero_pfiz_cnp_label' => esc_html__('CNP', 'curiero_pf_pj'),
            'curiero_pfiz_cnp_placeholder' => esc_html__('Introduceti Codul numeric personal', 'curiero_pf_pj'),
            'curiero_pfiz_cnp_visibility' => 'no',
            'curiero_pfiz_cnp_required' => 'no',
            'curiero_pfiz_cnp_error' => esc_html__('Datorita legislatiei in vigoare trebuie sa completati campul CNP', 'curiero_pf_pj'),
            'curiero_pjur_label' => esc_html__('Persoana Juridica', 'curiero_pf_pj'),
            'curiero_pjur_visibility' => 'yes',
            'curiero_pjur_company_label' => esc_html__('Nume Firma', 'curiero_pf_pj'),
            'curiero_pjur_company_placeholder' => esc_html__('Introduceti numele firmei dumneavoastra', 'curiero_pf_pj'),
            'curiero_pjur_company_visibility' => 'yes',
            'curiero_pjur_company_required' => 'yes',
            'curiero_pjur_company_error' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numele firmei dumneavoastra', 'curiero_pf_pj'),
            'curiero_pjur_cui_label' => esc_html__('CUI', 'curiero_pf_pj'),
            'curiero_pjur_cui_placeholder' => esc_html__('Introduceti Codul Unic de Inregistrare', 'curiero_pf_pj'),
            'curiero_pjur_cui_visibility' => 'yes',
            'curiero_pjur_cui_required' => 'yes',
            'curiero_pjur_cui_error' => esc_html__('Pentru a putea plasa comanda, avem nevoie de CUI-ul firmei dumneavoastra', 'curiero_pf_pj'),
            'curiero_pjur_nr_reg_com_label' => esc_html__('Nr. Reg. Com', 'curiero_pf_pj'),
            'curiero_pjur_nr_reg_com_placeholder' => 'J20/20/20.02.2020',
            'curiero_pjur_nr_reg_com_visibility' => 'yes',
            'curiero_pjur_nr_reg_com_required' => 'yes',
            'curiero_pjur_nr_reg_com_error' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numarul de ordine in registrul comertului', 'curiero_pf_pj'),
            'curiero_pjur_nume_banca_label' => esc_html__('Nume Banca', 'curiero_pf_pj'),
            'curiero_pjur_nume_banca_placeholder' => esc_html__('Numele bancii cu care lucrati', 'curiero_pf_pj'),
            'curiero_pjur_nume_banca_visibility' => 'no',
            'curiero_pjur_nume_banca_required' => 'no',
            'curiero_pjur_nume_banca_error' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numele bancii cu care lucrati', 'curiero_pf_pj'),
            'curiero_pjur_iban_label' => esc_html__('IBAN', 'curiero_pf_pj'),
            'curiero_pjur_iban_placeholder' => esc_html__('Numarul contului IBAN', 'curiero_pf_pj'),
            'curiero_pjur_iban_visibility' => 'no',
            'curiero_pjur_iban_required' => 'no',
            'curiero_pjur_iban_error' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numarul contului', 'curiero_pf_pj'),
            'curiero_pf_pj_output' => 'select',
            'curiero_pf_pj_default' => 'pers-fiz',
            'curiero_pf_pj_label' => esc_html__('Tip Client', 'curiero_pf_pj'),
        ];
    }

    public function curiero_settings_page_class(array $settings): array
    {
        $settings[] = require 'settings_page.class.php';

        return $settings;
    }

    public function register_curiero_admin_tabs(array $tabs_with_sections): array
    {
        $tabs_with_sections['curiero-pf-pj'] = ['', 'pers-fiz', 'pers-jur'];

        return $tabs_with_sections;
    }

    public function wc_admin_connect_page(): void
    {
        if (!function_exists('wc_admin_connect_page')) {
            return;
        }

        wc_admin_connect_page(
            [
                'id' => 'woocommerce-settings-curiero-pf-pj',
                'parent' => 'woocommerce-settings',
                'screen_id' => 'woocommerce_page_wc-settings-curiero-pf-pj',
                'title' => 'General', 'curiero_pf_pj',
                'path' => add_query_arg(
                    [
                        'page' => 'wc-settings',
                        'tab' => 'curiero-pf-pj',
                    ],
                    'admin.php'
                ),
            ]
        );

        wc_admin_connect_page(
            [
                'id' => 'woocommerce-settings-curiero-pf-pj-pers-fiz',
                'parent' => 'woocommerce-settings-curiero-pf-pj',
                'screen_id' => 'woocommerce_page_wc-settings-curiero-pf-pj-pers-fiz',
                'title' => __('Persoana Fizica', 'curiero_pf_pj'),
            ]
        );

        wc_admin_connect_page(
            [
                'id' => 'woocommerce-settings-curiero-pf-pj-pers-jur',
                'parent' => 'woocommerce-settings-curiero-pf-pj',
                'screen_id' => 'woocommerce_page_wc-settings-curiero-pf-pj-pers-jur',
                'title' => __('Persoana Juridica', 'curiero_pf_pj'),
            ]
        );
    }

    /**
     * Update the order meta with extra fields.
     */
    public function update_order_meta(int $order_id): void
    {
        $av_settings = [];

        if (!isset($_POST['curiero_pf_pj_type'])) {
            return;
        }

        $av_settings['curiero_pf_pj_type'] = sanitize_text_field($_POST['curiero_pf_pj_type'] ?? 'pf');

        if ('pers-fiz' === $_POST['curiero_pf_pj_type']) {
            if (isset($_POST['cnp']) && '' !== $_POST['cnp']) {
                $av_settings['cnp'] = sanitize_text_field($_POST['cnp']);
            }
        } elseif ('pers-jur' === $_POST['curiero_pf_pj_type']) {
            $av_settings['cui'] = '';
            if (isset($_POST['cui']) && '' !== $_POST['cui']) {
                $av_settings['cui'] = sanitize_text_field($_POST['cui']);
            }

            $av_settings['nr_reg_com'] = '';
            if (isset($_POST['nr_reg_com']) && '' !== $_POST['nr_reg_com']) {
                $av_settings['nr_reg_com'] = sanitize_text_field($_POST['nr_reg_com']);
            }

            $av_settings['nume_banca'] = '';
            if (isset($_POST['nume_banca']) && '' !== $_POST['nume_banca']) {
                $av_settings['nume_banca'] = sanitize_text_field($_POST['nume_banca']);
            }

            $av_settings['iban'] = '';
            if (isset($_POST['iban']) && '' !== $_POST['iban']) {
                $av_settings['iban'] = sanitize_text_field($_POST['iban']);
            }
        }

        if (!empty($av_settings)) {
            $order = curiero_get_order($order_id);
            $order->update_meta_data('curiero_pf_pj_option', $av_settings);
            if (class_exists('Smartbill_Woocommerce') || function_exists('smartbill_create_document')) {
                $order->update_meta_data('smartbill_billing_type', $av_settings['curiero_pf_pj_type'] === 'pers-jur' ? 'pj' : 'pf');
                if ($av_settings['curiero_pf_pj_type'] === 'pers-jur') {
                    $order->update_meta_data('smartbill_billing_cif', $av_settings['cui']);
                    $order->update_meta_data('smartbill_billing_company_name', sanitize_text_field($_POST['billing_company'] ?? ''));
                    $order->update_meta_data('smartbill_billing_nr_reg_com', $av_settings['nr_reg_com']);
                }
            }
            $order->save_meta_data();
        }
    }

    /**
     * Update the customer meta with extra fields.
     */
    public function update_customer_data(int $customer_id, array $data): void
    {
        $av_settings = [];

        if (!isset($data['curiero_pf_pj_type'])) {
            return;
        }

        $av_settings['curiero_pf_pj_type'] = $data['curiero_pf_pj_type'];

        if ('pers-fiz' == $data['curiero_pf_pj_type']) {
            if (isset($data['cnp']) && '' != $data['cnp']) {
                $av_settings['cnp'] = sanitize_text_field($data['cnp']);
            }
        } elseif ('pers-jur' == $data['curiero_pf_pj_type']) {
            if (isset($data['cui']) && '' != $data['cui']) {
                $av_settings['cui'] = sanitize_text_field($data['cui']);
            }

            if (isset($data['nr_reg_com']) && '' != $data['nr_reg_com']) {
                $av_settings['nr_reg_com'] = sanitize_text_field($data['nr_reg_com']);
            }

            if (isset($data['nume_banca']) && '' != $data['nume_banca']) {
                $av_settings['nume_banca'] = sanitize_text_field($data['nume_banca']);
            }

            if (isset($data['iban']) && '' != $data['iban']) {
                $av_settings['iban'] = sanitize_text_field($data['iban']);
            }
        }

        if (!empty($av_settings)) {
            foreach ($av_settings as $key => $value) {
                update_user_meta($customer_id, $key, sanitize_text_field($value));
            }
        }
    }

    /**
     * Filter billing fields.
     */
    public function filter_billing_fields(array $fields, WC_Abstract_Order $order): array
    {
        $defaults = [
            'cnp' => '',
            'cui' => '',
            'nr_reg_com' => '',
            'nume_banca' => '',
            'iban' => '',
        ];

        $data = $order->get_meta('curiero_pf_pj_option');
        $tip = isset($data['curiero_pf_pj_type']) ? $data['curiero_pf_pj_type'] : '';
        if (isset($data['curiero_pf_pj_type'])) {
            unset($data['curiero_pf_pj_type']);
        }

        if ('pers-fiz' === $tip && isset($fields['company'])) {
            $fields['company'] = '';
        }

        $extra_fields = wp_parse_args($data, $defaults);

        return array_merge($fields, $extra_fields);
    }

    public function myacc_filter_billing_fields(array $fields, int $customer_id): array
    {
        $user_type = get_user_meta($customer_id, 'curiero_pf_pj_type', true);

        if ('pers-fiz' === $user_type) {
            $fields['cnp'] = get_user_meta($customer_id, 'cnp', true);
            unset($fields['company']); // daca aleg persoana fizica, sa mi arate doar cnp ul
        } else {
            $fields['cui'] = get_user_meta($customer_id, 'cui', true);
            $fields['nr_reg_com'] = get_user_meta($customer_id, 'nr_reg_com', true);
            $fields['nume_banca'] = get_user_meta($customer_id, 'nume_banca', true);
            $fields['iban'] = get_user_meta($customer_id, 'iban', true);
        }

        return $fields;
    }

    /**
     * Add replacements for our extra fields.
     */
    public function extra_fields_replacements(array $replacements, array $args): array
    {
        $replacements['{cnp}'] = isset($args['cnp']) ? $args['cnp'] : '';
        $replacements['{cui}'] = isset($args['cui']) ? $args['cui'] : '';
        $replacements['{nr_reg_com}'] = isset($args['nr_reg_com']) ? $args['nr_reg_com'] : '';
        $replacements['{nume_banca}'] = isset($args['nume_banca']) ? $args['nume_banca'] : '';
        $replacements['{iban}'] = isset($args['iban']) ? $args['iban'] : '';

        return $replacements;
    }

    public function localisation_address_formats(array $formats): array
    {
        $formats['default'] = "{name}\n{cnp}\n{company}\n{cui}\n{nr_reg_com}\n{nume_banca}\n{iban}\n{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}";

        return $formats;
    }

    public function admin_billing_fields(array $fields): array
    {
        $options = get_option('curiero_pf_pj_option', []);
        $options = wp_parse_args($options, $this->defaults);

        $new_fields = [
            'curiero_pf_pj_type' => [
                'label' => __('Tip client', 'woocommerce'),
                'show' => false,
                'type' => 'select',
                'wrapper_class' => 'form-field-wide',
                'options' => [
                    'pers-fiz' => esc_html__('Persoana Fizica', 'curiero_pf_pj'),
                    'pers-jur' => esc_html__('Persoana Juridica', 'curiero_pf_pj'),
                ],
            ],
        ];

        foreach ($fields as $key => $field) {
            $new_fields[$key] = $field;
            if ('company' === $key) {

                if (isset($options['curiero_pfiz_cnp_visibility']) && 'yes' === $options['curiero_pfiz_cnp_visibility']) {
                    $new_fields['cnp'] = [
                        'label' => $options['curiero_pfiz_cnp_label'],
                        'wrapper_class' => 'form-field-wide curiero_pf_pj_option_field show_if_pers-fiz',
                        'show' => false,
                    ];
                }

                if (isset($options['curiero_pjur_cui_visibility']) && 'yes' === $options['curiero_pjur_cui_visibility']) {
                    $new_fields['cui'] = [
                        'label' => $options['curiero_pjur_cui_label'],
                        'wrapper_class' => 'curiero_pf_pj_option_field show_if_pers-jur',
                        'show' => false,
                    ];
                }

                if (isset($options['curiero_pjur_nr_reg_com_visibility']) && 'yes' === $options['curiero_pjur_nr_reg_com_visibility']) {
                    $new_fields['nr_reg_com'] = [
                        'label' => $options['curiero_pjur_nr_reg_com_label'],
                        'wrapper_class' => 'last curiero_pf_pj_option_field show_if_pers-jur',
                        'show' => false,
                    ];
                }

                if (isset($options['curiero_pjur_nume_banca_visibility']) && 'yes' === $options['curiero_pjur_nume_banca_visibility']) {
                    $new_fields['nume_banca'] = [
                        'label' => $options['curiero_pjur_nume_banca_label'],
                        'wrapper_class' => 'curiero_pf_pj_option_field show_if_pers-jur',
                        'show' => false,
                    ];
                }

                if (isset($options['curiero_pjur_iban_visibility']) && 'yes' === $options['curiero_pjur_iban_visibility']) {
                    $new_fields['iban'] = [
                        'label' => $options['curiero_pjur_iban_label'],
                        'wrapper_class' => 'last curiero_pf_pj_option_field show_if_pers-jur',
                        'show' => false,
                    ];
                }
            }
        }

        return $new_fields;
    }

    public function admin_billing_get_curiero_pf_pj_type($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_tip($object->get_id());

        return $value;
    }

    public function admin_billing_get_cnp($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_cnp($object->get_id());

        return $value;
    }

    public function admin_billing_get_cui($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_cui($object->get_id());

        return $value;
    }

    public function admin_billing_get_nume_banca($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_nume_banca($object->get_id());

        return $value;
    }

    public function admin_billing_get_nr_reg_com($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_nr_reg_com($object->get_id());

        return $value;
    }

    public function admin_billing_get_iban($value, WC_Abstract_Order $object)
    {
        $options_helper = CurieRO_PFPJ_Options::get_instance();
        $value = $options_helper->get_iban($object->get_id());

        return $value;
    }

    public function save_admin_billing_fields(int $order_id): void
    {
        $av_settings = [];

        if (!isset($_POST['_billing_curiero_pf_pj_type'])) {
            return;
        }

        $av_settings['curiero_pf_pj_type'] = sanitize_text_field($_POST['_billing_curiero_pf_pj_type']);
        unset($_POST['_billing_curiero_pf_pj_type']);

        if ('pers-fiz' === $av_settings['curiero_pf_pj_type']) {
            if (isset($_POST['_billing_cnp']) && '' != $_POST['_billing_cnp']) {
                $av_settings['cnp'] = sanitize_text_field($_POST['_billing_cnp']);
                unset($_POST['_billing_cnp']);
            }
        } elseif ('pers-jur' === $av_settings['curiero_pf_pj_type']) {
            $fields = ['_billing_cui', '_billing_nr_reg_com', '_billing_iban', '_billing_nume_banca'];
            foreach ($fields as $field_key) {
                $av_key = str_replace('_billing_', '', $field_key);
                if (isset($_POST[$field_key])) {
                    $av_settings[$av_key] = sanitize_text_field($_POST[$field_key]);
                    unset($_POST[$field_key]);
                }
            }
        }

        $order = curiero_get_order($order_id);
        $order->update_meta_data('curiero_pf_pj_option', $av_settings);
        $order->save_meta_data();
    }

    public function admin_enqueue_scripts(string $hook): void
    {
        $screen = get_current_screen();
        if ('post.php' !== $hook) {
            return;
        }

        if ('shop_order' !== $screen->post_type) {
            return;
        }

        wp_enqueue_script('curiero_pf_pj', CURIERO_PF_PJ_ASSETS . 'js/admin.js', ['jquery'], '1.0.0');
    }
}

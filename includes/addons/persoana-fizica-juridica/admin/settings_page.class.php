<?php

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('CurieRO_PFPJ_Admin_Settings_Page', false)) {
    return CurieRO()->container->get(CurieRO_PFPJ_Admin_Settings_Page::class);
}

class CurieRO_PFPJ_Admin_Settings_Page extends WC_Settings_Page
{
    public function __construct()
    {
        $this->id = 'curiero-pf-pj';
        $this->label = esc_html__('CurieRO Persoana fizica/juridica', 'curiero_pf_pj');

        parent::__construct();
    }

    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections()
    {
        $sections = [
            '' => esc_html__('General', 'curiero_pf_pj'),
            'pers-fiz' => esc_html__('Persoana Fizica', 'curiero_pf_pj'),
            'pers-jur' => esc_html__('Persoana Juridica', 'curiero_pf_pj'),
        ];

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Output the settings.
     */
    public function output(): void
    {
        global $current_section;

        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * Save settings.
     */
    public function save(): void
    {
        global $current_section;

        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::save_fields($settings);

        if ($current_section) {
            do_action('woocommerce_update_options_' . $this->id . '_' . $current_section);
        }
    }

    /**
     * Get settings array.
     *
     * @param string $current_section Current section name.
     * @return array
     */
    public function get_settings($current_section = '')
    {
        if ('pers-fiz' === $current_section) {
            $settings = [
                [
                    'title' => esc_html__('Setari Persoane Fizice', 'curiero_pf_pj'),
                    'type' => 'title',
                    'id' => 'curiero_pfiz_start',
                ],
                [
                    'name' => esc_html__('Label Persoana Fizica', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Persoana Fizica', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_label]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pfiz_end',
                ],

                [
                    'title' => esc_html__('Camp CNP', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pfiz_cnp_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('CNP', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_cnp_label]',
                ],
                [
                    'name' => esc_html__('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Introduceti Codul numeric personal', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_cnp_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_cnp_visibility]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>CNP</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_cnp_required]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Datorita legislatiei in vigoare trebuie sa completati campul CNP', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pfiz_cnp_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pfiz_cnp_end',
                ],
            ];
        } elseif ('pers-jur' === $current_section) {
            $settings = [
                [
                    'title' => esc_html__('Setari Persoane Juridice', 'curiero_pf_pj'),
                    'type' => 'title',
                    'id' => 'curiero_pjur_start',
                ],
                [
                    'name' => esc_html__('Label Persoana Juridica', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Persoana Juridica', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_label]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_end',
                ],

                // Nume Firma
                [
                    'title' => esc_html__('Camp Nume Firma', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pjur_company_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Nume Firma', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_company_label]',
                ],
                [
                    'name' => esc_html__('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Introduceti numele firmei dumneavoastra', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_company_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_company_visibility]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>Nume Firma</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_company_required]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numele firmei dumneavoastra', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_company_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_company_end',
                ],

                // CUI
                [
                    'title' => esc_html__('Camp CUI', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pjur_cui_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('CUI', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_cui_label]',
                ],
                [
                    'name' => __('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Introduceti Codul Unic de Inregistrare', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_cui_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_cui_visibility]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>CUI</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_cui_required]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Pentru a putea plasa comanda, avem nevoie de CUI-ul firmei dumneavoastra', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_cui_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_cui_end',
                ],

                // Nr. Reg. Com.
                [
                    'title' => esc_html__('Camp Nr. Reg. Com.', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pjur_nr_reg_com_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Nr. Reg. Com', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nr_reg_com_label]',
                ],
                [
                    'name' => esc_html__('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => 'J20/20/20.02.2020',
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nr_reg_com_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nr_reg_com_visibility]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>Nr. Reg. Com</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nr_reg_com_required]',
                    'default' => 'yes',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numarul de ordine in registrul comertului', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nr_reg_com_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_nr_reg_com_end',
                ],

                // Nume Banca
                [
                    'title' => esc_html__('Camp Nume Banca', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pjur_nume_banca_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Nume Banca', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nume_banca_label]',
                ],
                [
                    'name' => esc_html__('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Numele bancii cu care lucrati', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nume_banca_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nume_banca_visibility]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>Nume Banca</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nume_banca_required]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numele bancii cu care lucrati', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_nume_banca_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_nume_banca_end',
                ],

                // IBAN
                [
                    'title' => esc_html__('Camp IBAN', 'curiero_pf_pj'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => 'curiero_pjur_iban_start',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('IBAN', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_iban_label]',
                ],
                [
                    'name' => esc_html__('Placeholder', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Numarul contului IBAN', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_iban_placeholder]',
                ],
                [
                    'title' => esc_html__('Vizibilitate', 'curiero_pf_pj'),
                    'desc' => esc_html__('Arata acest camp pe pagina de checkout', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_iban_visibility]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'title' => esc_html__('Obligatoriu', 'curiero_pf_pj'),
                    'desc' => __('Da, campul <strong>IBAN</strong> este Obligatoriu', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_iban_required]',
                    'default' => 'no',
                    'type' => 'checkbox',
                ],
                [
                    'name' => esc_html__('Mesaj Eroare', 'curiero_pf_pj'),
                    'type' => 'textarea',
                    'default' => esc_html__('Pentru a putea plasa comanda, avem nevoie de numarul contului', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pjur_iban_error]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero_pjur_iban_end',
                ],

            ];
        } else {
            $settings = [
                [
                    'title' => esc_html__('Setari Generale', 'curiero_pf_pj'),
                    'type' => 'title',
                    'id' => 'curiero-pf-pj_general_start',
                ],
                [
                    'name' => esc_html__('Tip camp', 'curiero_pf_pj'),
                    'type' => 'select',
                    'options' => [
                        'radio' => esc_html__('Butoane radio', 'curiero_pf_pj'),
                        'select' => esc_html__('Select', 'curiero_pf_pj'),
                        'hidden' => esc_html__('Doar persoana fizica', 'curiero_pf_pj'),
                    ],
                    'default' => 'select',
                    'desc' => __('<p>Cum va fi afisata optiunea de a alege intre persoana fizica sau juridica</p>', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pf_pj_output]',
                ],
                [
                    'name' => esc_html__('Optiune implicita', 'curiero_pf_pj'),
                    'type' => 'select',
                    'options' => [
                        'pers-fiz' => esc_html__('Persoana Fizica', 'curiero_pf_pj'),
                        'pers-jur' => esc_html__('Persoana Juridica', 'curiero_pf_pj'),
                    ],
                    'desc' => __('<p>Optiunea care va fi selectata implicit pe pagina de checkout</p>', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pf_pj_default]',
                ],
                [
                    'name' => esc_html__('Label', 'curiero_pf_pj'),
                    'type' => 'text',
                    'default' => esc_html__('Tip Client', 'curiero_pf_pj'),
                    'id' => 'curiero_pf_pj_option[curiero_pf_pj_label]',
                ],
                [
                    'type' => 'sectionend',
                    'id' => 'curiero-pf-pj_general_end',
                ],
            ];
        }

        return apply_filters('woocommerce_get_settings_' . $this->id, $settings, $current_section);
    }
}

return CurieRO()->container->get(CurieRO_PFPJ_Admin_Settings_Page::class);

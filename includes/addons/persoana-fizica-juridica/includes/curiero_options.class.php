<?php

class CurieRO_PFPJ_Options
{
    private $options = [];

    private static $_instance;

    public static function get_instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }

    public function get_keys()
    {
        return apply_filters('curiero_pfpj_options_keys', ['_curiero_pf_pj_option_cnp', '_curiero_pf_pj_option_nr_reg_com', '_curiero_pf_pj_option_cui', '_curiero_pf_pj_option_nume_banca', '_curiero_pf_pj_option_iban']);
    }

    public function get_cnp(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['cnp']) ? $this->options[$order_id]['cnp'] : '';
    }

    public function get_nr_reg_com(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['nr_reg_com']) ? $this->options[$order_id]['nr_reg_com'] : '';
    }

    public function get_cui(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['cui']) ? $this->options[$order_id]['cui'] : '';
    }

    public function get_nume_banca(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['nume_banca']) ? $this->options[$order_id]['nume_banca'] : '';
    }

    public function get_iban(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['iban']) ? $this->options[$order_id]['iban'] : '';
    }

    public function get_tip(int $order_id): string
    {
        if (empty($this->options[$order_id])) {
            $order = curiero_get_order($order_id);
            $this->options[$order_id] = $order->get_meta('curiero_pf_pj_option', true);
        }

        return !empty($this->options[$order_id]['curiero_pf_pj_type']) ? $this->options[$order_id]['curiero_pf_pj_type'] : '';
    }
}

CurieRO_PFPJ_Options::get_instance();

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_City_Select
{
    public const VERSION = '1.1.6';

    private $cities;

    public function __construct()
    {
        add_filter('woocommerce_checkout_fields', [$this, 'checkout_fields'], 100, 1);
        add_filter('woocommerce_form_field_city', [$this, 'form_field_city'], 100, 4);

        add_filter('woocommerce_states', [$this, 'romanian_woocommerce_states']);
        add_filter('woocommerce_default_address_fields', [$this, 'state_city_field_order']);

        add_action('wp_enqueue_scripts', [$this, 'load_scripts'], PHP_INT_MAX);
    }

    public function checkout_fields(?array $fields = []): array
    {
        $fields['billing']['billing_city']['type'] = 'city';
        $fields['shipping']['shipping_city']['type'] = 'city';

        return $fields;
    }

    public function get_cities(?string $cc = null): array
    {
        if (empty($this->cities)) {
            global $cities, $wpdb;

            $query = "SELECT county_initials,locality_name FROM {$wpdb->prefix}curiero_localities WHERE 1=1 ";
            $active_shipping_methods = CurieRO()->shipping_methods->get_active();

            if (count($active_shipping_methods) === 1) {
                if (in_array(Cargus_Shipping_Method::class, $active_shipping_methods)) {
                    $query .= 'AND cargus_locality_id IS NOT NULL';
                } elseif (in_array(Fan_Shipping_Method::class, $active_shipping_methods)) {
                    $query .= 'AND fan_locality_id IS NOT NULL';
                } elseif (in_array(Sameday_Shipping_Method::class, $active_shipping_methods)) {
                    $query .= 'AND sameday_locality_id IS NOT NULL';
                }
            }

            $query = apply_filters('wc_city_select_query', trim($query));
            $cityList = $wpdb->get_results($query);
            $cities['RO'] = [];

            foreach ($cityList as $city) {
                $cities['RO'][$city->county_initials][] = $city->locality_name;
            }

            $this->cities = apply_filters('wc_city_select_cities', $cities);
        }

        if (!empty($cc)) {
            return isset($this->cities[$cc]) ? $this->cities[$cc] : [];
        } else {
            return $this->cities;
        }
    }

    public function form_field_city(string $field, string $key, array $args, string $value): string
    {
        // Do we need a clear div?
        if ((!empty($args['clear']))) {
            $after = '<div class="clear"></div>';
        } else {
            $after = '';
        }

        // Required markup
        if ($args['required']) {
            $args['class'][] = 'validate-required';
            $required = ' <abbr class="required" title="' . esc_attr__('required', 'woocommerce') . '">*</abbr>';
        } else {
            $required = '';
        }

        // Custom attribute handling
        $custom_attributes = [];
        if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
            foreach ($args['custom_attributes'] as $attribute => $attribute_value) {
                $custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
            }
        }

        // Validate classes
        if (!empty($args['validate'])) {
            foreach ($args['validate'] as $validate) {
                $args['class'][] = 'validate-' . $validate;
            }
        }

        // field p and label
        $field = '<p class="form-row ' . esc_attr(implode(' ', $args['class'])) . '" id="' . esc_attr($args['id']) . '_field">';
        if ($args['label']) {
            $field .= '<label for="' . esc_attr($args['id']) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . $required . '</label>';
        }

        // Get Country
        $subkey = substr($key, 0, strlen('billing')) === 'billing' ? 'billing' : 'shipping';
        $current_country = WC()->checkout->get_value("{$subkey}_country") ?: (WC()->session->get('customer')['country'] ?? null) ?: 'RO';
        $cart_selected_county = WC()->checkout->get_value("{$subkey}_state") ?: (WC()->session->get('customer')['state'] ?? null);

        // Get country cities
        $cities = $this->get_cities($current_country);
        if (!empty($cities)) {
            $field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="city_select ' . esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' placeholder="' . __('Selectati localitatea&hellip;', 'woocommerce') . '"><option value="">' . __('Selectati localitatea&hellip;', 'woocommerce') . '</option>';

            if ($cart_selected_county && !empty($cities[$cart_selected_county])) {
                foreach ($cities[$cart_selected_county] as $city_name) {
                    $field .= '<option value="' . esc_attr($city_name) . '" ' . selected($value, $city_name, false) . '>' . $city_name . '</option>';
                }
            }

            $field .= '</select>';
        } else {
            $field .= '<input type="text" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" value="' . esc_attr($value) . '"  placeholder="' . esc_attr($args['placeholder']) . '" name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" ' . implode(' ', $custom_attributes) . ' />';
        }

        // field description and close wrapper
        if ($args['description']) {
            $field .= '<span class="description">' . esc_attr($args['description']) . '</span>';
        }

        $field .= '</p>' . $after;

        return $field;
    }

    public function romanian_woocommerce_states(array $states = []): array
    {
        return array_merge($states, ['RO' => curiero_get_counties_list()]);
    }

    public function state_city_field_order(array $fields = []): array
    {
        $fields['state']['priority'] = 80;
        $fields['city']['priority'] = 85;

        return $fields;
    }

    public function load_scripts(): void
    {
        if (is_cart() || is_checkout() || is_wc_endpoint_url('edit-address')) {
            wp_dequeue_script('selectWoo');
            wp_deregister_script('selectWoo');

            wp_enqueue_script('selectWoo', CURIERO_PLUGIN_URL . 'includes/addons/city_select/assets/js/selectWoo.full.min.js', ['jquery', 'woocommerce'], self::VERSION, true);
            wp_enqueue_script('wc-city-select', CURIERO_PLUGIN_URL . 'includes/addons/city_select/assets/js/city-select.min.js', ['jquery', 'woocommerce'], self::VERSION, true);

            wp_localize_script('wc-city-select', 'wc_city_select_params', [
                'cities' => json_encode($this->get_cities()),
                'i18n_select_city_text' => esc_attr__('Selectati localitatea&hellip;', 'woocommerce'),
            ]);
        }
    }
}

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Email_Methods
{
    /**
     * CurieRO_Email_Methods constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_notices', [$this, 'curiero_show_admin_email_notification'], 20);
        add_action('woocommerce_order_actions', [$this, 'add_send_awb_email_action']);
        add_action('woocommerce_order_action_curiero_send_awb_email', [$this, 'send_awb_email']);
        add_action('wp_ajax_curiero_reset_mail', [$this, 'reset_email_templates']);
        add_action('wp_ajax_curiero_send_awb_email', [$this, 'send_bulk_awb_emails']);
    }

    /**
     * Show admin notification for email sent.
     *
     * @return void
     */
    public function curiero_show_admin_email_notification(): void
    {
        if ($message = get_transient('email_sent_success')) { ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e($message, 'curiero-email-woocommerce'); ?></p>
            </div>
        <?php delete_transient('email_sent_success');
        }

        if ($message = get_transient('email_sent_error')) { ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e($message, 'curiero-email-woocommerce'); ?></p>
            </div>
        <?php delete_transient('email_sent_error');
        }
    }

    /**
     * Reset email templates.
     *
     * @return void
     */
    public function reset_email_templates(): void
    {
        if (!current_user_can('curiero_can_manage_options')) {
            wp_send_json_error('Invalid security token sent or insufficient permissions.', 403);
        }

        $courier = sanitize_text_field($_POST['courier']);

        $file_path = sprintf(
            '%s/includes/print_methods/%s/templates/default_email_template.php',
            untrailingslashit(CURIERO_PLUGIN_PATH),
            $courier
        );

        if (file_exists($file_path)) {
            require $file_path;
            delete_option($email_template_field);
            delete_option($email_subject_field);
            delete_option($email_title_field);
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Add send AWB email action.
     *
     * @param array $actions
     * @return array
     */
    public function add_send_awb_email_action(array $actions = []): array
    {
        return array_merge(
            $actions,
            ['curiero_send_awb_email' => __('Trimite email cu AWB-ul generat', 'curiero-plugin')]
        );
    }

    /**
     * Send AWB email.
     *
     * @param WC_Abstract_Order $order
     * @return void
     *
     * @throws Exception
     */
    public static function send_awb_email(WC_Abstract_Order $order): void
    {
        $email = $order->get_billing_email();
        $order_id = $order->get_ID();
        $cod_amount = $order->get_payment_method() === 'cod' ? $order->get_total() : 0;

        foreach (CurieRO()->printing_methods->get_active() as $alias => $active_courier_class) {
            if ($awb = $order->get_meta($active_courier_class::$awb_field, true)) {
                switch ($alias) {
                    case 'bookurier':
                        $awb_info = [
                            'email' => $email,
                            'rbs_val' => $cod_amount,
                        ];

                        break;

                    case 'cargus':
                        $awb_info = [
                            'Recipient' => ['Email' => $email],
                            'BankRepayment' => $cod_amount,
                        ];

                        break;

                    case 'dpd':
                        $awb_info = [
                            'recipient_email' => $email,
                            'cod_amount' => $cod_amount,
                        ];

                        break;

                    case 'fancourier':
                        if (get_option('fan_plata_transport') === 'destinatar' && $cod_amount !== 0) {
                            $cod_amount = number_format((float) $order->get_total() - $order->get_shipping_total() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '');
                        }
                        $awb_info = [
                            'mail' => $email,
                            'ramburs' => $cod_amount,
                        ];

                        break;

                    case 'mygls':
                    case 'gls':
                        $awb_info = [
                            'consig_email' => $email,
                            'codamount' => $cod_amount,
                        ];

                        break;

                    case 'sameday':
                        $awb_info = [
                            'email' => $email,
                            'cod_value' => $cod_amount,
                        ];

                        break;

                    case 'memex':
                        $awb_info = [
                            'shipmentRequest' => [
                                'ShipTo' => ['Email' => $email],
                                'COD' => ['Amount' => $cod_amount],
                            ],
                        ];

                        break;

                    case 'optimus':
                        $awb_info = [
                            'ramburs_valoare' => $cod_amount,
                        ];

                        break;

                    case 'express':
                        $awb_info = [
                            'to_email' => $email,
                            'ramburs' => $cod_amount,
                        ];

                        break;

                    case 'team':
                        $awb_info = [
                            'to_email' => $email,
                            'ramburs' => $cod_amount,
                        ];

                        break;

                    case 'innoship':
                        $awb_fields = maybe_unserialize($awb);
                        $awb = $awb_fields['awb'];
                        $awb_info = [
                            'email' => $email,
                            'cod_value' => $cod_amount,
                            'tracking_url' => $awb_fields['tracking_url'],
                            'courier_id' => $awb_fields['courier_id'],
                        ];

                        break;

                    default:
                        throw new Exception('Case unhandled.');
                }

                $active_courier_class::send_mails($order_id, $awb, $awb_info);

                return;
            }
        }
    }

    /**
     * Send bulk AWB emails.
     *
     * @return void
     *
     * @throws Exception
     */
    public function send_bulk_awb_emails(): void
    {
        if (
            !current_user_can('curiero_can_download_awb')
            || !check_ajax_referer('curiero_printing_ajax_nonce', '_wpnonce', false)
        ) {
            wp_send_json_error('Invalid security token sent or insufficient permissions.', 403);
        }

        $order_ids = $_POST['order_ids'] ?? [];

        foreach ($order_ids as $order_id) {
            self::send_awb_email(curiero_get_order($order_id['value']));
        }

        wp_send_json_success();
    }
}

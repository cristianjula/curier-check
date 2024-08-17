<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_AWB_Actions
{
    /**
     * CurieRO_AWB_Actions constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_page_handler']);
    }

    /**
     * Magic method for invoking AWB actions.
     *
     * @param string $action
     * @param mixed $args
     * @return void
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function __call(string $action, $args): void
    {
        [$courier, $order_id] = $args;

        if (!in_array($action, ['download', 'delete', 'generate'])) {
            throw new BadMethodCallException("Invalid Method provided. Invoked method: {$action}.");
        }

        if (!array_key_exists($courier, CurieRO()->printing_methods->get_active())) {
            throw new InvalidArgumentException("Invalid Courier provided. Invoked courier: {$courier}.");
        }

        if (empty($order_id)) {
            throw new BadMethodCallException("Invalid Order provided. Invoked order_id: {$order_id}.");
        }

        require CURIERO_PLUGIN_PATH . "includes/print_methods/{$courier}/actions/{$action}.php";
    }

    /**
     * Register page handler for AWB actions.
     *
     * @return void
     */
    public function register_page_handler(): void
    {
        $order_action_page = add_submenu_page(
            '',
            'CurieRO Order Actions',
            'CurieRO Order Actions',
            'curiero_can_interact_awb',
            'curiero-order-actions',
            function (): void {},
        );

        add_action("load-{$order_action_page}", function () {
            $action = $_GET['action'] ?? null;
            $order_id = $_GET['order_id'] ?? null;
            $courier = $_GET['courier'] ?? null;

            if (empty($action)) {
                throw new InvalidArgumentException('Invalid Courier Action provided. Action cannot be empty.');
            }

            wc_nocache_headers();

            curiero_check_nonce_capability(
                "curiero_{$action}_awb_{$order_id}",
                "curiero_can_{$action}_awb"
            );

            return $this->{$action}($courier, $order_id);
        });
    }
}

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_Permissions
{
    /** Roles that will be granted permissions. */
    private const ROLES = [
        'administrator', // Core WordPress Administrator
        'shop_manager', // WooCommerce Shop Manager
    ];

    /** Permissions that will be granted. */
    private const PERMISSIONS = [
        'curiero_can_manage_options', // Required to edit plugin options
        'curiero_can_interact_awb', // Required to interact with AWB pages
        'curiero_can_generate_awb', // Required to generate AWBs
        'curiero_can_download_awb', // Required to download AWBs
        'curiero_can_delete_awb', // Required to delete AWBs
    ];

    /**
     * Add plugin permissions.
     *
     * @return void
     */
    public static function add_plugin_permissions(): void
    {
        foreach (self::ROLES as $role) {
            $role = get_role($role);
            if (!$role) {
                continue;
            }

            foreach (self::PERMISSIONS as $permission) {
                $role->add_cap($permission, true);
            }
        }
    }

    /**
     * Remove plugin permissions.
     *
     * @return void
     */
    public static function remove_plugin_permissions(): void
    {
        foreach (self::ROLES as $role) {
            $role = get_role($role);
            if (!$role) {
                continue;
            }

            foreach (self::PERMISSIONS as $permission) {
                $role->remove_cap($permission);
            }
        }
    }
}

<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_DB_Update
{
    /**
     * Old plugin version.
     *
     * @var mixed
     */
    public $old_version = '1.0.0';

    /**
     * CurieRO_Plugin_DB_Update constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'curiero_plugin_db_update'], -1);
    }

    /**
     * Update plugin database.
     *
     * @return void
     */
    public function curiero_plugin_db_update(): void
    {
        $this->old_version = get_option('CURIERO_DB_VER', CURIERO_DB_VER);

        $this->curiero_drop_legacy_db();

        if (version_compare(CURIERO_DB_VER, $this->old_version, '!=')) {
            CurieRO_Plugin_Database::create_database();
        }
    }

    /**
     * Drop legacy database.
     *
     * @return void
     */
    private function curiero_drop_legacy_db(): void
    {
        global $wpdb;

        if (version_compare('1.1.3', $this->old_version, '>=')) {
            $sql_delete_county = 'DROP TABLE IF EXISTS `courier_counties`;';
            $wpdb->query($sql_delete_county);

            $sql_delete_localities = 'DROP TABLE IF EXISTS `courier_localities`;';
            $wpdb->query($sql_delete_localities);

            $sql_delete_zipcodes = 'DROP TABLE IF EXISTS `courier_zipcodes`;';
            $wpdb->query($sql_delete_zipcodes);
        }
    }
}

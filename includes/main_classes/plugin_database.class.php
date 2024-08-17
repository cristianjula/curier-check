<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_Database
{
    /** Query limit for inserting data into database. */
    private const QUERY_LIMIT = 300;

    /**
     * Create plugin database.
     *
     * @return void
     */
    public static function create_database(): void
    {
        self::drop_database();
        self::create_tables();
        self::insert_localities();
        self::insert_zipcodes();

        update_option('CURIERO_DB_VER', CURIERO_DB_VER);
    }

    /**
     * Drop plugin database.
     *
     * @return void
     */
    public static function drop_database(): void
    {
        global $wpdb;

        $sql_delete_localities = "DROP TABLE IF EXISTS `{$wpdb->prefix}curiero_localities`";
        $wpdb->query($sql_delete_localities);

        $sql_delete_zipcodes = "DROP TABLE IF EXISTS `{$wpdb->prefix}curiero_zipcodes`";
        $wpdb->query($sql_delete_zipcodes);

        // Ensure that the last tables are dropped
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}curiero_zipcodes'") === "{$wpdb->prefix}curiero_zipcodes") {
            sleep(1);
        }
    }

    /**
     * Create plugin tables.
     *
     * @return void
     */
    private static function create_tables(): void
    {
        global $wpdb;

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}curiero_localities` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `fan_locality_id` int(15) DEFAULT NULL,
                `cargus_locality_id` int(15) DEFAULT NULL,
                `sameday_locality_id` int(15) DEFAULT NULL,
                `locality_name` varchar(125) NOT NULL,
                `fan_locality_name` varchar(125) NULL DEFAULT NULL,
                `cargus_locality_name` varchar(125) NULL DEFAULT NULL,
                `sameday_locality_name` varchar(125) NULL DEFAULT NULL,
                `county_initials` varchar(2) NOT NULL,
                `county_name` varchar(75) NOT NULL,
                `fan_extra_km` int(11) DEFAULT NULL,
                `cargus_extra_km` int(11) DEFAULT NULL,
                `sameday_extra_km` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `county_locality_combo` (`county_initials`,`locality_name`),
                INDEX `fan_locality_id` (`fan_locality_id`),
                INDEX `cargus_locality_id` (`cargus_locality_id`),
                INDEX `sameday_locality_id` (`sameday_locality_id`)
            ) ENGINE=InnoDB DEFAULT COLLATE=utf8mb4_unicode_ci;"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}curiero_zipcodes` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `County` VARCHAR(25) NOT NULL,
                `City`  VARCHAR(50) NOT NULL,
                `Street` VARCHAR(150) DEFAULT NULL,
                `ZipCode` VARCHAR(10) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `County_City_ZipCode` (`County`, `City`, `ZipCode`)
            ) ENGINE=InnoDB DEFAULT COLLATE=utf8mb4_unicode_ci;"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}curiero_mygls_awb_data` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `order_id` int(11) unsigned DEFAULT NULL,
                `awb_number` varchar(20) DEFAULT NULL,
                `awb_data` mediumblob DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `order_id` (`order_id`)
              ) ENGINE=InnoDB DEFAULT COLLATE=utf8mb4_unicode_ci;"
        );
    }

    /**
     * Insert localities into database.
     *
     * @return void
     */
    private static function insert_localities(): void
    {
        global $wpdb;

        $localities_response = curiero_make_request(curiero_get_api_url('/v1/localities'));
        $localities_response = json_decode(wp_remote_retrieve_body($localities_response), true) ?? [];

        $query_start = "INSERT IGNORE INTO `{$wpdb->prefix}curiero_localities` (`fan_locality_id`, `cargus_locality_id`, `sameday_locality_id`, `locality_name`, `fan_locality_name`, `cargus_locality_name`, `sameday_locality_name`, `county_initials`, `county_name`, `fan_extra_km`, `cargus_extra_km`, `sameday_extra_km`) VALUES ";

        foreach (array_chunk($localities_response, static::QUERY_LIMIT) as $localities_chunk) {
            $query_values = '';

            foreach ($localities_chunk as $locality) {
                $locality['fan_locality_name'] = !empty($locality['fan_locality_name']) ? "'" . $locality['fan_locality_name'] . "'" : 'NULL';
                $locality['cargus_locality_name'] = !empty($locality['cargus_locality_name']) ? "'" . $locality['cargus_locality_name'] . "'" : 'NULL';
                $locality['sameday_locality_name'] = !empty($locality['sameday_locality_name']) ? "'" . $locality['sameday_locality_name'] . "'" : 'NULL';

                $query_values .= '(' . ($locality['fan_locality_id'] ?? 'NULL') . ', ' . ($locality['cargus_locality_id'] ?? 'NULL') . ', ' . ($locality['sameday_locality_id'] ?? 'NULL') . ", '" . ($locality['locality_name']) . "', " . ($locality['fan_locality_name']) . ', ' . ($locality['cargus_locality_name']) . ', ' . ($locality['sameday_locality_name']) . ", '" . ($locality['county_initials']) . "', '" . ($locality['county_name']) . "', " . ($locality['fan_extra_km'] ?? 'NULL') . ', ' . ($locality['cargus_extra_km'] ?? 'NULL') . ', ' . ($locality['sameday_extra_km'] ?? 'NULL') . '),';
            }

            $query_values = rtrim($query_values, ',');
            $wpdb->query($query_start . $query_values);
        }
    }

    /**
     * Insert zipcodes into database.
     *
     * @return void
     */
    private static function insert_zipcodes(): void
    {
        global $wpdb;

        $zipcode_response = curiero_make_request(curiero_get_api_url('/v1/zipcodes'));
        $zipcode_response = json_decode(wp_remote_retrieve_body($zipcode_response), true) ?? [];

        $query_start = "INSERT IGNORE INTO `{$wpdb->prefix}curiero_zipcodes` (`County`, `City`, `Street`, `ZipCode`) VALUES ";

        foreach (array_chunk($zipcode_response, static::QUERY_LIMIT) as $zipcode_chunk) {
            $query_values = '';

            foreach ($zipcode_chunk as $zipcode) {
                $zipcode['Street'] = !empty($zipcode['Street']) ? "'" . $zipcode['Street'] . "'" : 'NULL';
                $zipcode['ZipCode'] = str_pad($zipcode['ZipCode'], 6, '0', STR_PAD_LEFT);

                $query_values .= "('" . ($zipcode['County']) . "', '" . ($zipcode['City']) . "', " . ($zipcode['Street']) . ", '" . ($zipcode['ZipCode']) . "'),";
            }

            $query_values = rtrim($query_values, ',');
            $wpdb->query($query_start . $query_values);
        }
    }
}

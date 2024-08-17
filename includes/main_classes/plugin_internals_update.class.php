<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

class CurieRO_Plugin_Internals_Update
{
    /**
     * Old plugin version.
     *
     * @var string
     */
    public $old_version = '1.0.0';

    /**
     * CurieRO_Plugin_Internals_Update constructor.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('after_setup_theme', [$this, 'curiero_plugin_internals_update'], -1);
    }

    /**
     * Update plugin internals.
     *
     * @return void
     */
    public function curiero_plugin_internals_update(): void
    {
        $this->old_version = get_option('CURIERO_INTERNALS_VER', CURIERO_INTERNALS_VER);

        if (version_compare(CURIERO_INTERNALS_VER, $this->old_version, '=')) {
            return;
        }

        $this->curiero_sameday_update_240222();
        $this->curiero_internals_update_100822();
        $this->curiero_disable_email_autoloader_130922();
        $this->curiero_fancourier_clientid_update_090323();
        $this->curiero_permissions_update_090823();
        $this->curiero_memex_additionalservices_update_270324();

        CurieRO_Plugin_States::remove_transients();
        CurieRO_Plugin_States::clear_container_cache();

        update_option('CURIERO_INTERNALS_VER', CURIERO_INTERNALS_VER);
    }

    /**
     * Update Sameday service id.
     *
     * @return void
     */
    private function curiero_sameday_update_240222(): void
    {
        if (version_compare('1.0.1', $this->old_version, '>')) {
            if (!get_option('sameday_service_id')) {
                return;
            }

            $sameday_service_id = get_option('sameday_service_id');
            $sameday_additional_services = get_option('sameday_additional_services');
            $sameday_lockers_services = ['16', '15', '17', '24', '30', '31', '38'];

            if (in_array($sameday_service_id, $sameday_lockers_services)) {
                add_option('sameday_locker_service_id', $sameday_service_id);
                add_option('sameday_locker_additional_services', $sameday_additional_services);
            } else {
                add_option('sameday_ord_service_id', $sameday_service_id);
                add_option('sameday_ord_additional_services', $sameday_additional_services);
            }

            delete_option('sameday_service_id');
        }
    }

    /**
     * Update plugin internals.
     *
     * @return void
     */
    private function curiero_internals_update_100822(): void
    {
        if (version_compare('1.0.2', $this->old_version, '>')) {
            $schedules = ['curiero_gls_awb_update', 'curiero_fan_courier_awb_update', 'curiero_urgent_cargus_awb_update', 'curiero_dpd_awb_update', 'curiero_sameday_awb_update', 'curiero_memex_awb_update', 'curiero_optimus_awb_update', 'curiero_express_awb_update', 'curiero_team_awb_update', 'curiero_bookurier_awb_update', 'curiero_memex_call_pickup', 'curiero_fetch_dpd_box', 'curiero_fetch_sameday_easybox'];
            foreach ($schedules as $schedule) {
                wp_clear_scheduled_hook($schedule);
            }
        }
    }

    /**
     * Disable email autoloader.
     *
     * @return void
     */
    private function curiero_disable_email_autoloader_130922(): void
    {
        if (version_compare('1.0.3', $this->old_version, '>')) {
            $change_autoload = function (string $option_name, bool $autoload_value): void {
                $options_value = get_option($option_name, '');
                if (empty($options_value)) {
                    return;
                }
                // Wordpress does not allow to change to autoload if the value have not been changed as well
                // We should change the value to something else, then change it back again to its original value
                update_option($option_name, $options_value . ((strpos($option_name, 'template') !== false) ? '<br/>' : '.'));
                usleep(50000);
                update_option($option_name, $options_value, $autoload_value);
            };

            $option_list = ['bookurier_email_template', 'uc_email_template', 'dpd_email_template', 'express_email_template', 'fan_email_template', 'GLS_email_template', 'memex_email_template', 'optimus_email_template', 'sameday_email_template', 'team_email_template', 'bookurier_subiect_mail', 'uc_subiect_mail', 'dpd_subiect_mail', 'express_subiect_mail', 'fan_subiect_mail', 'GLS_subiect_mail', 'memex_subiect_mail', 'optimus_subiect_mail', 'sameday_subiect_mail', 'team_subiect_mail'];

            foreach ($option_list as $option) {
                $change_autoload($option, false);
            }
        }
    }

    /**
     * Update Fancourier client id.
     *
     * @return void
     */
    private function curiero_fancourier_clientid_update_090323(): void
    {
        if (version_compare('1.0.4', $this->old_version, '>')) {
            if (get_option('fan_clientID')) {
                update_option('fan_valid_auth', '1');
            }
        }
    }

    /**
     * Update plugin permissions.
     *
     * @return void
     */
    private function curiero_permissions_update_090823(): void
    {
        if (version_compare('1.0.6', $this->old_version, '>')) {
            CurieRO_Plugin_Permissions::add_plugin_permissions();
        }
    }

    /**
     * Update Memex sms service.
     *
     * @return void
     */
    private function curiero_memex_additionalservices_update_270324(): void
    {
        if (version_compare('1.0.9', $this->old_version, '>')) {
            $old_service = get_option('memex_additional_sms', 'Nu');
            if ($old_service === 'Nu') {
                return;
            }

            $addition_services = get_option('memex_additional_services', []) ?: [];
            if ($old_service === 'Da' && !in_array('SSMS', $addition_services)) {
                $addition_services[] = 'SSMS';
                update_option('memex_additional_services', $addition_services);
                delete_option('memex_additional_sms');
            }
        }
    }
}

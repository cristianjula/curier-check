<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$fan_print = get_option('enable_fan_print');
$fan_shipping = get_option('enable_fan_shipping');
$memex_print = get_option('enable_memex_print');
$memex_shipping = get_option('enable_memex_shipping');
$cargus_print = get_option('enable_cargus_print');
$cargus_shipping = get_option('enable_cargus_shipping');
$gls_print = get_option('enable_gls_print');
$mygls_print = get_option('enable_mygls_print');
$mygls_shipping = get_option('enable_mygls_shipping');
$gls_shipping = get_option('enable_gls_shipping');
$dpd_print = get_option('enable_dpd_print');
$dpd_shipping = get_option('enable_dpd_shipping');
$sameday_print = get_option('enable_sameday_print');
$sameday_shipping = get_option('enable_sameday_shipping');
$innoship_print = get_option('enable_innoship_print');
$innoship_shipping = get_option('enable_innoship_shipping');
$bookurier_print = get_option('enable_bookurier_print');
$bookurier_shipping = get_option('enable_bookurier_shipping');
$optimus_print = get_option('enable_optimus_print');
$optimus_shipping = get_option('enable_optimus_shipping');
$express_print = get_option('enable_express_print');
$express_shipping = get_option('enable_express_shipping');
$team_print = get_option('enable_team_print');
$team_shipping = get_option('enable_team_shipping');
$enable_checkout_city_select = get_option('enable_checkout_city_select');
$enable_pers_fiz_jurid = get_option('enable_pers_fiz_jurid');
$disable_zipcode_in_checkout = get_option('disable_zipcode_in_checkout');
$enable_automatic_smartbill = get_option('enable_automatic_smartbill');
$enable_automatic_oblio = get_option('enable_automatic_oblio');
$enable_automatic_fgo = get_option('enable_automatic_fgo');
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<style>
    .wc-shipping-class-slug img {max-width:92px;vertical-align:middle;}
    <?= get_option('auth_validity') == false ? '.hideOnFail { display: none; }' : ''; ?>
</style>

<div class="wrap">
    <h1>CurieRO - Setari generale</h1>
    <br>
    <form action="options.php" method="post">
        <?php
            settings_fields('curiero_settings');
        ?>
        <input type="hidden" name="auth_validity" value="<?= get_option('auth_validity'); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h3 style="margin:5px 0">CurieRO</h3></th>
                    <td class="wc-shipping-class-slug">
                        <img src="<?= CURIERO_PLUGIN_URL ?>/assets/images/banner-772x250.png" alt="FanCourier" style="max-height: 34px;max-width: 112px;">
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator:</th>
                    <td><input type="text" name="user_curiero" value="<?= get_option('user_curiero'); ?>" size="50" placeholder="Numele utilizatorului CurieRO"/></td>
                </tr>

                <tr>
                    <th align="left">Parola:</th>
                    <td><input type="password" name="password_curiero" value="<?= get_option('password_curiero'); ?>" size="50" placeholder="Parola utilizatorului CurieRO"/></td>
                </tr>

                <tr>
                    <th align="left" class="responseHereApi"></th>
                    <td align="right">
                        <button type="button" name="validate_api" class="button">Valideaza credentialele</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="hideOnFail">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">FanCourier</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/fancourier/assets/images/fancourier.png" alt="FanCourier" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_fan_print">
                                <option value="0" <?= $fan_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $fan_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_fan_shipping">
                                <option value="0" <?= $fan_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $fan_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Cargus</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/cargus/assets/images/cargus_logo.svg" alt="Cargus" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_cargus_print">
                                <option value="0" <?= $cargus_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $cargus_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_cargus_shipping">
                                <option value="0" <?= $cargus_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $cargus_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">GLS Online</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/gls/assets/images/gls-button.png" alt="GLS" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_gls_print">
                                <option value="0" <?= $gls_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $gls_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_gls_shipping">
                                <option value="0" <?= $gls_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $gls_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">MyGLS</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/mygls/assets/images/logo_mygls.svg" alt="MyGLS" style="max-height:36px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_mygls_print">
                                <option value="0" <?= $mygls_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $mygls_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_mygls_shipping">
                                <option value="0" <?= $mygls_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $mygls_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">DPD</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/dpd/assets/images/dpd.svg" alt="DPD" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_dpd_print">
                                <option value="0" <?= $dpd_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $dpd_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_dpd_shipping">
                                <option value="0" <?= $dpd_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $dpd_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Sameday</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/sameday/assets/images/sameday.png" alt="Sameday" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_sameday_print">
                                <option value="0" <?= $sameday_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $sameday_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_sameday_shipping">
                                <option value="0" <?= $sameday_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $sameday_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Innoship</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/innoship/assets/images/innoship.png" alt="Innoship" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_innoship_print">
                                <option value="0" <?= $innoship_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $innoship_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_innoship_shipping">
                                <option value="0" <?= $innoship_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $innoship_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Bookurier</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/bookurier/assets/images/logo_bookurier.svg" alt="Bookurier" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_bookurier_print">
                                <option value="0" <?= $bookurier_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $bookurier_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_bookurier_shipping">
                                <option value="0" <?= $bookurier_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $bookurier_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">PTT Express</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/memex/assets/images/ptt_logo.png" alt="Memex" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_memex_print">
                                <option value="0" <?= $memex_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $memex_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_memex_shipping">
                                <option value="0" <?= $memex_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $memex_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">OptimusCourier</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/optimus/assets/images/optimuslogo.png" alt="OptimusCourier" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_optimus_print">
                                <option value="0" <?= $optimus_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $optimus_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_optimus_shipping">
                                <option value="0" <?= $optimus_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $optimus_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">ExpressCourier</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/express/assets/images/logo_express.png" alt="ExpressCourier" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_express_print">
                                <option value="0" <?= $express_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $express_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_express_shipping">
                                <option value="0" <?= $express_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $express_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">TeamCourier</h3></th>
                        <td class="wc-shipping-class-slug">
                            <img src="<?= CURIERO_PLUGIN_URL ?>includes/print_methods/team/assets/images/logo_team.png" alt="TeamCourier" style="max-height: 34px;">
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Generare AWB:</th>
                        <td>
                            <select name="enable_team_print">
                                <option value="0" <?= $team_print == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $team_print == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th align="left">Metoda de livrare:</th>
                        <td>
                            <select name="enable_team_shipping">
                                <option value="0" <?= $team_shipping == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $team_shipping == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Setari Extra</h3></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Persoana fizica/juridica in checkout:</th>
                        <td>
                            <select name="enable_pers_fiz_jurid">
                                <option value="0" <?= $enable_pers_fiz_jurid == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $enable_pers_fiz_jurid == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Ascunde cod poștal în cos si checkout:</th>
                        <td>
                            <input type="hidden" name="disable_zipcode_in_checkout" value="<?= $disable_zipcode_in_checkout; ?>">
                            <select name="disable_zipcode_in_checkout">
                                <option value="0" <?= $disable_zipcode_in_checkout == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $disable_zipcode_in_checkout == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Listă de orașe în checkout:
                                <?= wc_help_tip('Această funcționalitate este activată automat, iar setarea este IGNORATĂ, atunci când orice metodă de livrare este activă.') ?>
                            </div>
                        </th>
                        <td>
                            <?php
                            $shipping_active = count(CurieRO()->shipping_methods->get_active());
                            ?>
                            <input type="hidden" name="enable_checkout_city_select" value="<?= $enable_checkout_city_select; ?>">
                            <select name="enable_checkout_city_select" <?= $shipping_active ? 'disabled="disabled"' : ''; ?>>
                                <option value="0" <?= $enable_checkout_city_select == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $enable_checkout_city_select == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Generare factura la livrare - Smartbill:
                                <?= wc_help_tip('Activeaza generarea automata a facturii Smartbill in momentul in care comanda are statusul livrat.') ?>
                            </div>
                        </th>
                        <td>
                            <input type="hidden" name="enable_automatic_smartbill" value="<?= class_exists('Smartbill_Woocommerce') ? $enable_automatic_smartbill : '0'; ?>">
                            <?php if (class_exists('Smartbill_Woocommerce')) { ?>
                            <select name="enable_automatic_smartbill">
                                <option value="0" <?= $enable_automatic_smartbill == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $enable_automatic_smartbill == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                            <?php } else { ?>
                                <input type="text" disabled style="font-weight: 500;" value="Plugin-ul Smartbill trebuie sa fie instalat si activ." />
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Generare factura la livrare - Oblio:
                                <?= wc_help_tip('Activeaza generarea automata a facturii Oblio in momentul in care comanda are statusul livrat.') ?>
                            </div>
                        </th>
                        <td>
                            <input type="hidden" name="enable_automatic_oblio" value="<?= is_plugin_active('woocommerce-oblio/woocommerce-oblio.php') ? $enable_automatic_oblio : '0'; ?>">
                            <?php if (is_plugin_active('woocommerce-oblio/woocommerce-oblio.php')) { ?>
                            <select name="enable_automatic_oblio">
                                <option value="0" <?= $enable_automatic_oblio == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $enable_automatic_oblio == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                            <?php } else { ?>
                                <input type="text" disabled style="font-weight: 500;" value="Plugin-ul Oblio trebuie sa fie instalat si activ." />
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                        <div class="has_help_tip">
                                Generare factura la livrare - FGO:
                                <?= wc_help_tip('Activeaza generarea automata a facturii FGO in momentul in care comanda are statusul livrat.') ?>
                            </div>
                        </th>
                        <td>
                            <input type="hidden" name="enable_automatic_fgo" value="<?= is_plugin_active('woocommerce-fgo-premium/woocommerce-fgo-premium.php') ? $enable_automatic_fgo : '0'; ?>">
                            <?php if (is_plugin_active('woocommerce-fgo-premium/woocommerce-fgo-premium.php')) { ?>
                            <select name="enable_automatic_fgo">
                                <option value="0" <?= $enable_automatic_fgo == '0' ? 'selected="selected"' : ''; ?>>Inactiva</option>
                                <option value="1" <?= $enable_automatic_fgo == '1' ? 'selected="selected"' : ''; ?>>Activa</option>
                            </select>
                            <?php } else { ?>
                                <input type="text" disabled style="font-weight: 500;" value="Plugin-ul FGO trebuie sa fie instalat si activ." />
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h3 style="margin:5px 0">Raport de asistenta</h3></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="padding:1rem 1.5rem">
                            <textarea id="textReport" cols="30" rows="10" readonly="true" style="resize: none;"><?= CurieRO_Admin_Report::generate_report(); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Trimite raport pentru asistenta:</th>
                        <td>
                            <input id="sendReport" type="button" class="button button-secondary" value="Trimite raport">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr>
                <td colspan="2">
                <?php submit_button('Salveaza setarile'); ?>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:center;">
                    <p>© Copyright <script>document.write(new Date().getFullYear());</script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                </td>
            </tr>
        </table>

    </form>
</div>

<script>'function'===typeof window.jQuery&&jQuery("form select").each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
    jQuery(($) => {
        $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Salvam setarile..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Salveaza setarile")},5e3)});

        const curiero_url = "<?= curiero_get_api_url('/v1/auth/validate/curiero'); ?>";
        const user_field = $('input[name="user_curiero"]'),
            pass_field = $('input[name="password_curiero"]'),
            validity_field = $('input[name="auth_validity"]'),
            responseHereApi = $('.responseHereApi');

        $('form').on('submit', async (e) => await validateRequest().catch(() => false));

        $('#sendReport').on('click', async function() {
            const formData = new FormData();
            formData.append('action', 'curiero_send_report');

            const request = await fetch(ajaxurl, {
                method: 'POST',
                body: formData
            });

            const { success } = await request.json();
            if (success) {
                $('#sendReport').prop('disabled', true).val('✓ Raportul a fost trimis cu succes.').css('cssText', 'color: green !important; opacity: 0.75;');
            } else {
                $('#sendReport').prop('disabled', true).val('✕ Raportul nu a fost trimis cu success.').css('cssText', 'color: red !important; opacity: 0.75;');
            }
        });

        $('button[name="validate_api"]').on('click', async function() {
            const isValid = await validateRequest().catch(() => false);
            if (isValid) {
                $('input[type="submit"]').click();
            }
        });

        const validateRequest = function () {
            return new Promise(async (resolve, reject) => {
                const request = await fetch(curiero_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        api_user: user_field.val(),
                        api_pass: pass_field.val()
                    }),
                });

                const { message, success } = await request.json();
                responseHereApi.text(message);
                if (success) {
                    responseHereApi.css('color', '#34a934');
                    user_field.attr('style', '');
                    pass_field.attr('style', '');
                    validity_field.val(1);
                    resolve(true);
                } else {
                    responseHereApi.css('color', '#f44336');
                    pass_field.css('box-shadow', '0 0 2px 2px rgba(228, 7, 7, 0.45)');
                    user_field.css('box-shadow', '0 0 2px 2px rgba(228, 7, 7, 0.45)');
                    validity_field.val(0);
                    reject(false);
                }
            });
        }
    });
</script>

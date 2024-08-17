<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('bookurier', 'generate', $order_id) ?>">

        <input type="hidden" name="awb[client]" value="<?=$awb_details['client']?>" />
        <input type="hidden" name="awb[exchange_pack]" value="<?=$awb_details['exchange_pack']?>" />
        <input type="hidden" name="awb[confirmation]" value="<?=$awb_details['confirmation']?>" />

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Destinatar</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row">Nume:</th>
                    <td><input type="text" name="awb[recv]" value="<?=$awb_details['recv']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[street]" value="<?=$awb_details['street']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Judet:</th>
                    <td><input type="text" name="awb[district]" value="<?=$awb_details['district']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Localitate:</th>
                    <td><input type="text" name="awb[city]" value="<?=$awb_details['city']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[zip]" value="<?=$awb_details['zip']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Tara:</th>
                    <td><input type="text" name="awb[country]" value="<?=$awb_details['country']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[phone]" value="<?=$awb_details['phone']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[email]" value="<?=$awb_details['email']?>" size="40" /></td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Optiuni</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row">Informatii aditionale:</th>
                    <td><input type="text" name="awb[notes]" value="<?=$awb_details['notes']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Referinta comanda:</th>
                    <td><input type="text" name="awb[unq]" value="<?=$awb_details['unq']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare Ramburs:</th>
                    <td><input type="text" name="awb[rbs_val]" value="<?=$awb_details['rbs_val']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Numar colete:</th>
                    <td><input type="number" step="1" min="0" name="awb[packs]" value="<?=$awb_details['packs']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Greutate colete:</th>
                    <td><input type="number" step="1" min="0" name="awb[weight]" value="<?=$awb_details['weight']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare asigurata:</th>
                    <td><input type="number" min="0" name="awb[insurance_val]" value="<?=$awb_details['insurance_val']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Serviciul de livrare:</th>
                    <?php $service = get_option('bookurier_services'); ?>
                    <td>
                        <select name="awb[service]">
                            <option value="1" <?= selected(1, $service, false) ?>>Bucuresti 24h</option>
                            <option value="3" <?= selected(3, $service, false) ?>>Metropolitan</option>
                            <option value="5" <?= selected(5, $service, false) ?>>Extins</option>
                            <option value="7" <?= selected(7, $service, false) ?>>Bucuresti Today</option>
                            <option value="8" <?= selected(8, $service, false) ?>>National Economic</option>
                            <option value="9" <?= selected(9, $service, false) ?>>National Standard</option>
                            <option value="11" <?= selected(11, $service, false) ?>>National Premium</option>
                        </select>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?= submit_button('Generează AWB'); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <p>© Copyright <script>document.write(new Date().getFullYear());</script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<script>'function'===typeof window.jQuery&&jQuery("form select").each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
    jQuery(document).ready(function($) {
        $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});
    });
</script>
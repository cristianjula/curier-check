<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>


<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('optimus', 'generate', $order_id) ?>">

        <input type="hidden" name="awb[ref_factura]" value="<?= $awb_details['ref_factura']; ?>" />

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Destinatar</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Nume:</th>
                    <td><input type="text" name="awb[destinatar_nume]" value="<?= $awb_details['destinatar_nume'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[destinatar_adresa]" value="<?= $awb_details['destinatar_adresa'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Localitate:</th>
                    <td><input type="text" name="awb[destinatar_localitate]" value="<?= $awb_details['destinatar_localitate'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Judet:</th>
                    <td><input type="text" name="awb[destinatar_judet]" value="<?= $awb_details['destinatar_judet'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[destinatar_cod_postal]" value="<?= $awb_details['destinatar_cod_postal'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana de contact:</th>
                    <td><input type="text" name="awb[destinatar_contact]" value="<?= $awb_details['destinatar_contact'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[destinatar_telefon]" value="<?= $awb_details['destinatar_telefon'];?>"></td>
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
                <tr>
                    <th scope="row">Numar colete:</th>
                    <td><input type="number" name="awb[colet_buc]" value="<?= $awb_details['colet_buc'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Greutate:</th>
                    <td><input type="number" step="0.01" name="awb[colet_greutate]" value="<?= $awb_details['colet_greutate'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Continut:</th>
                    <td><input type="text"  name="awb[colet_descriere]" value="<?= $awb_details['colet_descriere'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Data colectare:</th>
                    <td><input type="date" name="awb[data_colectare]" value="<?= $awb_details['data_colectare'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Valoare ramburs:</th>
                    <td><input type="number" step="0.01" name="awb[ramburs_valoare]" value="<?= $awb_details['ramburs_valoare'];?>"></td>
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

<script>'function'===typeof window.jQuery&&jQuery('form select').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery(function($){
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});
})
</script>

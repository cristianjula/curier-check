<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('team', 'generate', $order_id) ?>">
        <input type="hidden" name="awb[payer]" value="<?=$awb_details['payer']?>" />
        <input type="hidden" name="awb[ramburs_type]" value="<?=$awb_details['ramburs_type']?>" />
        <input type="hidden" name="awb[insurance]" value="<?=$awb_details['insurance']?>" />
        <input type="hidden" name="awb[content]" value="<?=$awb_details['content']?>" />
        <input type="hidden" name="awb[from_name]" value="<?=$awb_details['from_name']?>" />
        <input type="hidden" name="awb[from_contact]" value="<?=$awb_details['from_contact']?>" />
        <input type="hidden" name="awb[from_email]" value="<?=$awb_details['from_email']?>" />
        <input type="hidden" name="awb[from_phone]" value="<?=$awb_details['from_phone']?>" />
        <input type="hidden" name="awb[from_county]" value="<?=$awb_details['from_county']?>" />
        <input type="hidden" name="awb[from_city]" value="<?=$awb_details['from_city']?>" />
        <input type="hidden" name="awb[from_address]" value="<?=$awb_details['from_address']?>" />
        <input type="hidden" name="awb[from_zipcode]" value="<?=$awb_details['from_zipcode']?>" />

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
                    <td><input type="text" name="awb[to_name]" value="<?= $awb_details['to_name'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana de contact:</th>
                    <td><input type="text" name="awb[to_contact]" value="<?= $awb_details['to_contact'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[to_phone]" value="<?= $awb_details['to_phone'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[to_email]" value="<?= $awb_details['to_email'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Judet:</th>
                    <td><input type="text" name="awb[to_county]" value="<?= $awb_details['to_county'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Oras:</th>
                    <td><input type="text" name="awb[to_city]" value="<?= $awb_details['to_city'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[to_zipcode]" value="<?= $awb_details['to_zipcode'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[to_address]" value="<?= $awb_details['to_address'];?>"></td>
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
                    <th align="left">Tip colet:</th>
                    <td>
                        <select name="awb[type]">
                            <option value="envelope" <?= $awb_details['type'] == 'envelope' ? 'selected' : '' ?> >Plic</option>
                            <option value="package" <?= $awb_details['type'] == 'package' ? 'selected' : '' ?>>Colet</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Serviciu:</th>
                    <td>
                        <select name="awb[service_type]">
                        <?php
                            $services = CurieRO()->container->get(APITeamClass::class)->get_services();
                            $current_service = $awb_details['service_type'];

                            if (!empty($services)) {
                                foreach($services as $service) {
                                    $selected = ($service['value'] == $current_service) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['value']}' {$selected}>{$service['name']}</option>";
                                }
                            } else {
                                ?>
                                <option value="Eco" <?= $current_service == 'Eco' ? 'selected="selected"' : ''; ?>>Eco</option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Retur:</th>
                    <td>
                        <select name="awb[retur]">
                            <option value="true" <?= $awb_details['retur'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['retur'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr id='tipRetur' <?= $awb_details['retur'] == 'false' ? 'style="display: none;"' : '' ?>>
                    <th scope="row">Tip retur:</th>
                    <td>
                        <select name="awb[retur_type]">
                            <option value="document" <?= $awb_details['retur_type'] == 'document' ? 'selected' : '' ?> >Document</option>
                            <option value="colet" <?= $awb_details['retur_type'] == 'colet' ? 'selected' : '' ?> >Colet</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Platitor expeditie:</th>
                    <td>
                        <select name="awb[payer]">
                            <option value="expeditor" <?=$awb_details['payer'] == 'expeditor' ? 'selected="selected"' : '';  ?> >Expeditor</option>
                            <option value="destinatar" <?=$awb_details['payer'] == 'destinatar' ? 'selected="selected"' : '';  ?> >Destinatar</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Deschidere colet:</th>
                    <td>
                        <select name="awb[service_41]">
                            <option value="true" <?= $awb_details['service_41'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_41'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Livrare sambata:</th>
                    <td>
                        <select name="awb[service_42]">
                            <option value="true" <?= $awb_details['service_42'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_42'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Taxa urgent express:</th>
                    <td>
                        <select name="awb[service_51]">
                            <option value="true" <?= $awb_details['service_51'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_51'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Schimbare adresa de livrare:</th>
                    <td>
                        <select name="awb[service_62]">
                            <option value="true" <?= $awb_details['service_62'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_62'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Ora speciala de livrare:</th>
                    <td>
                        <select name="awb[service_63]">
                            <option value="true" <?= $awb_details['service_63'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_63'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Swap (colet la schimb):</th>
                    <td>
                        <select name="awb[service_64]">
                            <option value="true" <?= $awb_details['service_64'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_64'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Retur confirmare de primire:</th>
                    <td>
                        <select name="awb[service_66]">
                            <option value="true" <?= $awb_details['service_66'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_66'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Retur documente:</th>
                    <td>
                        <select name="awb[service_67]">
                            <option value="true" <?= $awb_details['service_67'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_67'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">A 3-a livrare nationala:</th>
                    <td>
                        <select name="awb[service_73]">
                            <option value="true" <?= $awb_details['service_73'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_73'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Retur expediere/colet nelivrat:</th>
                    <td>
                        <select name="awb[service_84]">
                            <option value="true" <?= $awb_details['service_84'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_84'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Adaugare AWB agent TeamCourier:</th>
                    <td>
                        <select name="awb[service_104]">
                            <option value="true" <?= $awb_details['service_104'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_104'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Etichetare colet\plic la sediu Team Courier:</th>
                    <td>
                        <select name="awb[service_108]">
                            <option value="true" <?= $awb_details['service_108'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_108'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Colete aditionale:</th>
                    <td>
                        <select name="awb[service_292]">
                            <option value="true" <?= $awb_details['service_292'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['service_292'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Pachet fragil:</th>
                    <td>
                        <select name="awb[fragile]">
                            <option value="true" <?= $awb_details['fragile'] == 'true' ? 'selected' : '' ?> >Da</option>
                            <option value="false" <?= $awb_details['fragile'] == 'false' ? 'selected' : '' ?> >Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Valoare ramburs:</th>
                    <td><input type="number" step="0.01" name="awb[ramburs]" value="<?= $awb_details['ramburs'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Valoare asigurare:</th>
                    <td><input type="text" name="awb[insurance]" value="<?=$awb_details['insurance']?>"></td>
                </tr>

                <tr>
                    <th scope="row">Numar de pachete:</th>
                    <td><input type="number" name="awb[cnt]" value="<?= $awb_details['cnt'];?>"></td>
                </tr>

                <tr>
                    <th scope="row">Greutate totala (kg):</th>
                    <td><input type="number" name="awb[weight]" value="<?= $awb_details['weight'];?>"></td>
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
jQuery(function($) {
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});

    $('select[name="awb[retur]"]').on('change', () => {
        this.value === 'true' ? $('#tipRetur').show() : $('#tipRetur').hide();
    });
})
</script>

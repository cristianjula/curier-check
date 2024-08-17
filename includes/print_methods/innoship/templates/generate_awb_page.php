<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$innoship = CurieRO()->container->get(CurieroInnoshipClass::class);
$innoship_api = CurieRO()->container->get(APIInnoshipClass::class);

$locker_list = $innoship_api->getLockers($awb_details['county'], $awb_details['city']);
if (str_contains($awb_details['city'], 'Sector')) {
    $locker_list = array_merge($locker_list, $innoship_api->getLockers($awb_details['county'], 'Bucuresti'));
}
$locker_list = collect($locker_list)->unique('id')->sortBy('name');

?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>

    <?php if (!empty($awb_details['locker_id'])) { ?>
    <div class="notice notice-info locker-info">
        <h4>A fost selectata optiunea de Locker pentru aceasta comanda. Recomandam selectarea unui serviciu compatibil.</h4>
    </div>
    <?php } ?>
    <br>

    <form method="POST" action="<?= curiero_order_action_url('innoship', 'generate', $order_id) ?>">

        <input type="hidden" name="awb[order_id]" value="<?= $order_id ?>" />
        <input type="hidden" name="awb[country]" value="<?= $awb_details['country'] ?>" />
        <input type="hidden" name="awb[currency]" value="<?= $awb_details['currency'] ?>" />

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Expeditor</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Punct de ridicare:</th>
                    <td>
                        <select name="awb[location_id]">
                            <?php
                                $curiero_innoship_locations = $innoship_api->getClientLocations();
                                foreach ($curiero_innoship_locations as $innoship_location) {
                                    echo "<option value='{$innoship_location['externalLocationId']}'" . selected($innoship_location['externalLocationId'], $awb_details['location_id'], false) . ">{$innoship_location['name']}</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Curierul dorit:</th>
                    <td>
                        <select name="awb[courier_id]">
                            <option value="">Selectie inteligenta a curierului prin Innoship</option>
                            <?php
                                $client_courier_list = $innoship_api->getClientCouriers();
                                foreach ($client_courier_list as $client_courier_id => $client_courier) {
                                    echo "<option value='{$client_courier_id}'" . selected($client_courier_id, $awb_details['courier_id'], false) . ">{$client_courier}</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Seviciu:</th>
                    <td>
                        <select name="awb[service_id]">
                        <?php
                            $all_services = $innoship_api::getClientServices();
                            foreach ($all_services as $key => $service) {
                                echo "<option " . selected($key, $awb_details['service_id'], false) . " value='{$key}'>{$service}</option>";
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Punct Locker</th>
                    <td>
                        <select name="awb[locker_id]">
                        <option></option>
                        <?php foreach ($locker_list as $locker) { ?>
                            <option <?= selected($locker['id'], $awb_details['locker_id'] ?? null, true); ?> value="<?= esc_html($locker['id']); ?>">
                                <?= "{$locker['name']} - {$locker['addressText']}" ?>
                            </option>
                        <?php } ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Plata transport: </th>
                    <td>
                        <select name="awb[delivery_payer]">
                            <option value="sender" <?= $awb_details['delivery_payer'] == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="receiver" <?= $awb_details['delivery_payer'] == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Colectarea rambursului: </th>
                    <td>
                        <select name="awb[money_delivery_method]">
                            <option value="bank" <?= $awb_details['money_delivery_method'] == 'bank' ? 'selected="selected"' : ''; ?>>Cont colector</option>
                            <option value="cash" <?= $awb_details['money_delivery_method'] == 'cash' ? 'selected="selected"' : ''; ?>>Cash</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Deschidere la livrare:</th>
                    <td>
                        <select name="awb[open_on_arrival]">
                            <option value="false" <?= $awb_details['open_on_arrival'] == false ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="true" <?= $awb_details['open_on_arrival'] == true ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Livrare sambata:</th>
                    <td>
                        <select name="awb[saturday_delivery]">
                            <option value="false" <?= $awb_details['saturday_delivery'] == false ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="true" <?= $awb_details['saturday_delivery'] == true ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

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
                    <td><input type="text" name="awb[name]" value="<?= $awb_details['name'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Companie:</th>
                    <td><input type="text" name="awb[company]" value="<?= $awb_details['company'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[phone]" value="<?= $awb_details['phone'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[email]" value="<?= $awb_details['email'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Judet:</th>
                    <td><input type="text" name="awb[county]" value="<?= $awb_details['county'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Oras:</th>
                    <td><input type="text" name="awb[city]" value="<?= $awb_details['city'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[address]" value="<?= $awb_details['address'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[postcode]" value="<?= $awb_details['postcode'];?>"></td>
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
                    <th scope="row">Numar plicuri:</th>
                    <td><input type="number" min="0" name="awb[envelope_no]" value="<?=$awb_details['envelope_no']?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Numar colete:</th>
                    <td><input type="number" min="0" name="awb[package_no]" value="<?=$awb_details['package_no']?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Numar paleti:</th>
                    <td><input type="number" min="0" name="awb[palette_no]" value="<?=$awb_details['palette_no']?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Greutate:</th>
                    <td><input type="number" min="0" name="awb[weight]" value="<?=$awb_details['weight']?>"></td>
                </tr>

                <tr>
                    <th align="left">Tip ambalaj colet:</th>
                    <td><input type="text" name="awb[packaging]" value="<?= $awb_details['packaging'] ?>" size="50" placeholder="Ex: Carton" /></td>
                </tr>

                <tr>
                    <th align="left">Tip continut colet:</th>
                    <td><input type="text" name="awb[contents]" value="<?= $awb_details['contents'] ?>" size="50" placeholder="Ex: Obiecte de arta" /></td>
                </tr>

                <tr>
                    <th scope="row">Observatii (max 500 caractere):</th>
                    <td>
                        <textarea name="awb[observation]" lines="2" maxlength="500"><?= $awb_details['observation'];?></textarea>
                        <sub style="float:right;"><span class="letterCount">0</span>/500</sub>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare asigurata:</th>
                    <td><input type="number" step="0.01" name="awb[declared_value]" value="<?= $awb_details['declared_value'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare ramburs:</th>
                    <td><input type="number" step="0.01" name="awb[cod_value]" value="<?= $awb_details['cod_value'];?>"></td>
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

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="awb[locker_id]"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery($ => {
    $('form select[name="awb[locker_id]"]').select2({ placeholder: "Alege un Locker", allowClear: true });
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});

    const letterCountEl = document.querySelector('.letterCount');
    const innoshipObservationEl = document.querySelector('textarea[name="awb[observation]"]');

    letterCountEl.innerHTML = innoshipObservationEl.value.length;
    $(innoshipObservationEl).on('keyup change', () => letterCountEl.innerHTML = innoshipObservationEl.value.length);
});
</script>

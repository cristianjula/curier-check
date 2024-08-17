<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$services = CurieRO()->container->get(APIDPDClass::class)->get_services();
$dpd_boxes = collect();
$supported_countries = CurieRO()->container->get(APIDPDClass::class)->supported_countries;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('dpd', 'generate', $order_id) ?>">

        <input type="hidden" name="awb[language]" value="<?= $awb_details['language'] ?>" />
        <input type="hidden" name="awb[third_party_client_id]" value="<?= $awb_details['third_party_client_id'] ?>" />
        <input type="hidden" name="awb[package]" value="<?= $awb_details['package'] ?>" />
        <input type="hidden" name="awb[ref1]" value="<?= $awb_details['ref1'] ?>" />
        <input type="hidden" name="awb[autoadjust_pickup_date]" value="<?= $awb_details['autoadjust_pickup_date'] ?>" />
        <input type="hidden" name="awb[dropoff_office_id]" value="<?= $awb_details['dropoff_office_id'] ?>" />
        <input type="hidden" name="awb[recipient_address_country_id]" value="<?= $awb_details['recipient_address_country_id'] ?>" />

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0">Expeditor</h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Serviciu national:</th>
                    <td>
                        <select name="awb[service_id]">
                            <?php
                            $current_service_id = $awb_details['service_id'];

                            if (!empty($services)) {
                                foreach ($services as $service) {
                                    $selected = ($service['id'] == $current_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                            ?>
                                <option value="2505" <?= $current_service_id == '2505' ? 'selected="selected"' : ''; ?>>2505 - DPD STANDARD</option>
                                <option value="2002" <?= $current_service_id == '2002' ? 'selected="selected"' : ''; ?>>2002 - CLASIC NATIONAL</option>
                                <option value="2003" <?= $current_service_id == '2003' ? 'selected="selected"' : ''; ?>>2003 - CLASIC NATIONAL (COLET)</option>
                                <option value="2005" <?= $current_service_id == '2005' ? 'selected="selected"' : ''; ?>>2005 - CARGO NATIONAL</option>
                                <option value="2412" <?= $current_service_id == '2412' ? 'selected="selected"' : ''; ?>>2412 - PALLET ONE RO</option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Serviciu international:</th>
                    <td>
                        <select name="awb[international_service_id]">
                            <?php
                            $current_international_service_id = $awb_details['international_service_id'];

                            if (!empty($services)) {
                                foreach ($services as $service) {
                                    $selected = ($service['id'] == $current_international_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                            ?>
                                <option value="2212" <?= $current_international_service_id == '2212' ? 'selected="selected"' : ''; ?>>2212 - DPD REGIONAL CEE</option>
                                <option value="2303" <?= $current_international_service_id == '2303' ? 'selected="selected"' : ''; ?>>2303 - DPD INTERNATIONAL (RUTIER)</option>
                                <option value="2323" <?= $current_international_service_id == '2323' ? 'selected="selected"' : ''; ?>>2323 - CERERE DE COLECTARE INTERNATIONALA (RUTIER)</option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Punct expeditor:</th>
                    <td>
                        <select name="awb[sender_id]">
                            <?php
                            $senders = CurieRO()->container->get(APIDPDClass::class)->get_senders();
                            $current_sender_id = $awb_details['sender_id'];
                            if (!empty($senders)) {
                                foreach ($senders as $sender) {
                                    $selected = ($sender['clientId'] == $current_sender_id) ? 'selected="selected"' : '';
                                    $senderInfo = $sender['objectName'] ? ($sender['objectName'] . ' - ' .  $sender['address']['fullAddressString']) : $sender['address']['fullAddressString'];
                                    echo "<option value='{$sender['clientId']}' {$selected}>{$senderInfo}</option>";
                                }
                            } else {
                            ?>
                                <option <?= $current_sender_id == '' ? 'selected="selected"' : ''; ?>>Utilizator implicit</option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Plata transport:</th>
                    <td>
                        <select name="awb[courier_service_payer]">
                            <option value="SENDER" <?= $awb_details['courier_service_payer'] == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= $awb_details['courier_service_payer'] == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= $awb_details['courier_service_payer'] == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/tert</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Platitor ambalaj:</th>
                    <td>
                        <select name="awb[package_payer]">
                            <option value="SENDER" <?= $awb_details['package_payer'] == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= $awb_details['package_payer'] == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= $awb_details['package_payer'] == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/tert</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top" id="dpd_box">
                    <th scope="row">Punct DPD Box:</th>
                    <td>
                        <select name="awb[recipient_pickup_office_id]" id="awb[recipient_pickup_office_id]" style="width: 100%;">
                            <option></option>
                            <?php
                            $dpd_boxes = CurieRO()->container->get(CurieroDPDClass::class)->getDPDboxes();
                            foreach ($dpd_boxes as $dpd_box) : ?>
                                <option <?php selected($dpd_box['id'], $awb_details['recipient_pickup_office_id'] ?? null, true) ?> value="<?= esc_html($dpd_box['id']) ?>"> <?= ucwords(strtolower($dpd_box['address'])) . ' - ' . $dpd_box['name'] . " - Locker " . $dpd_box['id']  ?> </option>
                            <?php endforeach;
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0">Destinatar</h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Nume:</th>
                    <td><input type="text" name="awb[recipient_name]" value="<?= $awb_details['recipient_name']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana de contact:</th>
                    <td><input type="text" name="awb[recipient_contact]" value="<?= $awb_details['recipient_contact']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana privata:</th>
                    <td>
                        <select name="awb[recipient_private_person]">
                            <option value="y" <?= $awb_details['recipient_private_person'] == 'y' ? 'selected' : '' ?>>Da</option>
                            <option value="n" <?= $awb_details['recipient_private_person'] == 'n' ? 'selected' : '' ?>>Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[recipient_phone]" value="<?= $awb_details['recipient_phone']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[recipient_email]" value="<?= $awb_details['recipient_email']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Tara:</th>
                    <td>
                        <select name="awb[recipient_address_country_name]">
                        <?php

                        foreach ($supported_countries as $country_code => $country_name) {
                            $selected = ($awb_details['recipient_address_country_name'] == $country_code) ? 'selected="selected"' : '';
                            echo "<option value='{$country_code}' {$selected}>{$country_name['name'] }</option>";
                        }
                        ?>
                    </select>
                </td>


                </tr>
                <?php if($awb_details['recipient_address_country_name'] === 'Romania'): ?>
                    <tr id="dpd_state" >
                        <th scope="row">Judet:</th>
                        <td><input type="text" name="awb[recipient_address_state_id]" value="<?= $awb_details['recipient_address_state_id']; ?>"></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row">Oras:</th>
                    <td><input type="text" name="awb[recipient_address_site_name]" value="<?= $awb_details['recipient_address_site_name']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[recipient_address_postcode]" value="<?= $awb_details['recipient_address_postcode']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Adresa linia 1:</th>
                    <td><input type="text" name="awb[recipient_address_line1]" value="<?= $awb_details['recipient_address_line1']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Adresa linia 2:</th>
                    <td><input type="text" name="awb[recipient_address_line2]" value="<?= $awb_details['recipient_address_line2']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Notite adresa:</th>
                    <td><input type="text" name="awb[recipient_address_note]" value="<?= $awb_details['recipient_address_note']; ?>"></td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0">Optiuni</h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">Valoare asigurata:</th>
                    <td><input type="text" name="awb[declared_value_amount]" value="<?= $awb_details['declared_value_amount']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Optiuni inainte de livrare:</th>
                    <td>
                        <select name="awb[obpd_option]">
                            <option value="" <?= $awb_details['obpd_option'] == '' ? 'selected' : '' ?>>Nu</option>
                            <option value="open" <?= $awb_details['obpd_option'] == 'open' ? 'selected' : '' ?>>Deschidere colet la livrare</option>
                            <option value="test" <?= $awb_details['obpd_option'] == 'test' ? 'selected' : '' ?>>Testare produs la livrare</option>
                        </select>
                    </td>
                </tr>

                <tr id="dpd_obpd_return_service_id" <?= $awb_details['obpd_option'] ? '' : 'style="display: none;"' ?>>
                    <th scope="row">Serviciu implicit in caz de refuz expediere:</th>
                    <td>
                        <select name="awb[obpd_return_service_id]">
                            <?php
                            $services = CurieRO()->container->get(APIDPDClass::class)->get_services();
                            $current_service_id = $awb_details['obpd_return_service_id'];
                            if (!empty($services)) {
                                foreach ($services as $service) {
                                    $selected = ($service['id'] == $current_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                            ?>
                                <option value="2505" <?= $current_service_id == '2505' ? 'selected="selected"' : ''; ?>>2505 - DPD STANDARD</option>
                                <option value="2002" <?= $current_service_id == '2002' ? 'selected="selected"' : ''; ?>>2002 - CLASIC NATIONAL</option>
                                <option value="2003" <?= $current_service_id == '2003' ? 'selected="selected"' : ''; ?>>2003 - CLASIC NATIONAL (COLET)</option>
                                <option value="2005" <?= $current_service_id == '2005' ? 'selected="selected"' : ''; ?>>2005 - CARGO NATIONAL</option>
                                <option value="2412" <?= $current_service_id == '2412' ? 'selected="selected"' : ''; ?>>2412 - PALLET ONE RO</option>
                            <?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr id="dpd_obpd_return_payer" <?= $awb_details['obpd_option'] ? '' : 'style="display: none;"' ?>>
                    <th align="left">Plata transport in caz de refuz expediere:</th>
                    <td>
                        <select name="awb[obpd_return_payer]">
                            <option value="SENDER" <?= $awb_details['obpd_return_payer'] == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= $awb_details['obpd_return_payer'] == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= $awb_details['obpd_return_payer'] == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/tert</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Livrare sambata:</th>
                    <td>
                        <select name="awb[saturday_delivery]">
                            <option value="y" <?= $awb_details['saturday_delivery'] == 'y' ? 'selected' : '' ?>>Da</option>
                            <option value="n" <?= $awb_details['saturday_delivery'] == 'n' ? 'selected' : '' ?>>Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Pachet fragil:</th>
                    <td>
                        <select name="awb[declared_value_fragile]">
                            <option value="y" <?= $awb_details['declared_value_fragile'] == 'y' ? 'selected' : '' ?>>Da</option>
                            <option value="n" <?= $awb_details['declared_value_fragile'] == 'n' ? 'selected' : '' ?>>Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Valoare ramburs:</th>
                    <td><input type="number" step="0.01" name="awb[cod_amount]" value="<?= $awb_details['cod_amount']; ?>"></td>
                </tr>

                <tr id="dpd_codcurrency">
                    <th scope="row">Moneda:</th>
                    <td><input type="text" name="awb[cod_currency]" value="<?= $awb_details['cod_currency']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Continut:</th>
                    <td><input type="text" name="awb[contents]" value="<?= $awb_details['contents']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Numar de pachete:</th>
                    <td><input type="number" name="awb[parcels_count]" value="<?= $awb_details['parcels_count']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Greutate totala (kg):</th>
                    <td><input type="number" name="awb[total_weight]" value="<?= $awb_details['total_weight']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Nota colet (max 200 caractere):</th>
                    <td>
                        <textarea name="awb[shipmentNote]" lines="2" maxlength="200"><?= $awb_details['shipmentNote']; ?></textarea>
                        <sub style="float:right;"><span class="letterCount">0</span>/200</sub>
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
                        <p>© Copyright <script>
                                document.write(new Date().getFullYear());
                            </script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
</div>

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="awb[recipient_pickup_office_id]"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery(function($) {
    $('form select[name="awb[recipient_pickup_office_id]"]').selectWoo({ placeholder: "Alege un DPD Box" });
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});

	$('.letterCount').text($('textarea[name="awb[shipmentNote]"]').val().length);
	$('textarea[name="awb[shipmentNote]"]').on('keyup change', function() {
		$('.letterCount').text($(this).val().length);
	})
	$('select[name="awb[obpd_option]"]').on('change', function() {
		if ($(this).val() !== '') {
			$('#dpd_obpd_return_service_id').show();
			$('#dpd_obpd_return_payer').show();
		} else {
			$('#dpd_obpd_return_service_id').hide();
			$('#dpd_obpd_return_payer').hide();
		}
	});

    function show_or_hide_dpdbox()
    {
        if(jQuery.inArray( $('select[name="awb[service_id]"]').val(), ['2505', '2113']) !== -1) $('#dpd_box').show();
        else {
            $('#dpd_box').hide();
            $('select[name="awb[recipient_pickup_office_id]"]').val('').trigger('change');
        }
    }

    $('select[name="awb[service_id]"]').on('change', show_or_hide_dpdbox);
    $('select[name="awb[service_id]"]').trigger('change')

    const dpd_boxes_list = <?= count($dpd_boxes) ? collect($dpd_boxes) : collect() ?>;

    $('input[name="awb[recipient_address_site_name]"]').change(function(){
        let city = $(this).val().toUpperCase(),
            dpd_boxes = dpd_boxes_list.filter(dpd_box => dpd_box['city'] == city);

        $('select[name="awb[recipient_pickup_office_id]"] option').remove().end();
        $('select[name="awb[recipient_pickup_office_id]"]').append(new Option('', ''));
        dpd_boxes.forEach(dpd_box => {
            const new_option = new Option(dpd_box['address'], dpd_box['id']);
            $('select[name="awb[recipient_pickup_office_id]"]').append(new_option);
        });
        $('select[name="awb[recipient_pickup_office_id]"]').trigger('change');
    });
})
</script>
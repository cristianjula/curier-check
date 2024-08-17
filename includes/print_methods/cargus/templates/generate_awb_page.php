<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$courier_api = CurieRO()->container->get(UrgentCargusAPI::class);
$locker_list = CurieRO()->container->get(CurieroUCClass::class)->getPudoPoints();
$order = curiero_get_order($order_id);
$selected_locker_id = $order->get_meta('curiero_cargus_locker', true);
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>

    <?php if (!empty($selected_locker_id)) { ?>
    <div class="notice notice-info locker-info">
        <h4>A fost selectata optiunea de Cargus Ship & Go pentru aceasta comanda. Recomandam selectarea unui serviciu compatibil.</h4>
    </div>
    <?php } ?>
    <br>

    <form method="POST" action="<?= curiero_order_action_url('cargus', 'generate', $order_id) ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Expeditor</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row">Punct de lucru</th>
                    <?php
                        $resultLocations = $courier_api->callMethod('PickupLocations/GetForClient', [], 'GET');
                        $resultMessage = $resultLocations['message'];
                        $arrayResultLocations = json_decode($resultMessage, true);
                    ?>
                    <td>
                        <select name="awb[Sender][LocationId]"> <?php
                            foreach ($arrayResultLocations as $location) {
                                ?><option value="<?= $location['LocationId']; ?>" <?= $awb_details['Sender']['LocationId'] == $location['LocationId'] ? 'selected="selected"' : ''; ?>><?= $location['Name']; ?></option><?php
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Id Tarif</th>
                    <?php
                        $resultPriceTables = $courier_api->callMethod('PriceTables', [], 'GET');
                        $resultMessage = $resultPriceTables['message'];
                        $arrayPriceTables = json_decode($resultMessage, true);
                        if ($resultPriceTables['status'] == "200" && $resultMessage !== "Failed to authenticate!") {
                            ?> <td><select name="awb[PriceTableId]"> <?php
                            foreach ($arrayPriceTables as $price_table) {
                                ?><option value="<?= $price_table['PriceTableId']; ?>" <?= $awb_details['PriceTableId'] == $price_table['PriceTableId'] ? 'selected="selected"' : ''; ?>><?= $price_table['Name']; ?></option><?php
                            }
                            ?> </select></td> <?php
                        } else {
                            ?> <td><input type="text" name="awb[PriceTableId]" value="<?= $awb_details['PriceTableId']; ?>" size="50" /></td>
                    <?php } ?>
                </tr>
                <tr valign="top">
                    <th scope="row">Tip serviciu</th>
                    <td>
                        <select name="awb[ServiceId]">
                            <option value="34" <?= $awb_details['ServiceId'] == '34' ? 'selected="selected"' : ''; ?>>Economic Standard</option>
                            <option value="35" <?= $awb_details['ServiceId'] == '35' ? 'selected="selected"' : ''; ?>>Standard Plus</option>
                            <option value="36" <?= $awb_details['ServiceId'] == '36' ? 'selected="selected"' : ''; ?>>Palet Standard</option>
                            <option value="39" <?= $awb_details['ServiceId'] == '39' ? 'selected="selected"' : ''; ?>>Multipiece / Economic Standard M</option>
                            <option value="40" <?= $awb_details['ServiceId'] == '40' ? 'selected="selected"' : ''; ?>>Economic Standard M Plus</option>
                            <option value="1" <?= $awb_details['ServiceId'] == '1' ? 'selected="selected"' : ''; ?>>Standard</option>
                            <option value="4" <?= $awb_details['ServiceId'] == '4' ? 'selected="selected"' : ''; ?>>Business Partener</option>
                            <option value="38" <?= $awb_details['ServiceId'] == '38' ? 'selected="selected"' : ''; ?>>Ship & Go / PUDO Delivery</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Punct Ship & Go</th>
                    <td>
                        <select name="awb[DeliveryPudoPoint]">
                        <option value=""></option>
                        <?php foreach ($locker_list as $locker) { ?>
                            <option <?= selected($locker['Id'], $selected_locker_id ?? null, true); ?> value="<?= esc_html($locker['Id']); ?>">
                                <?= ucwords(strtolower($locker['City'])) . ' - ' . ucwords(strtolower($locker['Name'])); ?>
                            </option>
                        <?php } ?>
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
                    <th scope="row">Nume</th>
                    <td><input type="text" name="awb[Recipient][Name]" value="<?= $awb_details['Recipient']['Name'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Judet</th>
                    <td><input type="text" name="awb[Recipient][CountyName]" value="<?= $awb_details['Recipient']['CountyName'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Oras</th>
                    <td><input type="text" name="awb[Recipient][LocalityName]" value="<?= $awb_details['Recipient']['LocalityName'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Adresa</th>
                    <td><input type="text" name="awb[Recipient][AddressText]"  value="<?= $awb_details['Recipient']['AddressText'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Cod Postal</th>
                    <td><input type="text" name="awb[Recipient][CodPostal]"  value="<?= $awb_details['Recipient']['CodPostal'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Persoana contact</th>
                    <td><input type="text" name="awb[Recipient][ContactPerson]"  value="<?= $awb_details['Recipient']['ContactPerson'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Telefon</th>
                    <td><input type="text" name="awb[Recipient][PhoneNumber]"  value="<?= $awb_details['Recipient']['PhoneNumber'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Email</th>
                    <td><input type="text" name="awb[Recipient][Email]"  value="<?= $awb_details['Recipient']['Email'];?>"></td>
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
                    <th scope="row">Plicuri</th>
                    <td><input type="number" name="awb[Envelopes]" value="<?= $awb_details['Envelopes']; ?>" min="0" max="9"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Colete</th>
                    <td><input type="number" min="0" name="awb[Parcels]" value="<?= $awb_details['Parcels']; ?>"></td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:1rem 1.5rem;">
                        <table cellspacing="0" class="widefat urgent_parcel_size_table">
                            <tbody>
                                <tr <?php if(get_option('uc_nr_colete') < 1) echo 'style="display: none;"'; ?>>
                                    <th scope="col">Lungime (cm)</th>
                                    <th scope="col">Latime (cm)</th>
                                    <th scope="col">Inaltime (cm)</th>
                                    <th scope="col">Greutate (Kg)</th>
                                </tr>
                                <?php
                                for($i = 0; $i < get_option('uc_nr_colete'); $i++){
                                    $row_field_length = ($i == 0 ? $awb_details['ParcelCodes'][0]['Length'] : null);
                                    $row_field_width = ($i == 0 ? $awb_details['ParcelCodes'][0]['Width'] : null);
                                    $row_field_height = ($i == 0 ? $awb_details['ParcelCodes'][0]['Height'] : null);
                                    $row_field_weight = ($i == 0 ? $awb_details['ParcelCodes'][0]['Weight'] : null);
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="awb[ParcelCodes][<?= $i ?>][Code]" value="<?= $i ?>">
                                            <input type="hidden" name="awb[ParcelCodes][<?= $i ?>][Type]" value="1">
                                            <input type="text" name="awb[ParcelCodes][<?= $i ?>][Length]" value="<?= $row_field_length; ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="awb[ParcelCodes][<?= $i ?>][Width]" value="<?= $row_field_width; ?>" required>
                                        </td>
                                        <td>
                                            <input type="text" name="awb[ParcelCodes][<?= $i ?>][Height]" value="<?= $row_field_height; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[ParcelCodes][<?= $i ?>][Weight]" value="<?= $row_field_weight; ?>" required>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare asigurata</th>
                    <td><input type="text" name="awb[DeclaredValue]" value="<?= $awb_details['DeclaredValue'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Ramburs cash</th>
                    <td><input type="text" name="awb[CashRepayment]" value="<?= $awb_details['CashRepayment'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Ramburs cont colector</th>
                    <td><input type="text" name="awb[BankRepayment]" value="<?= $awb_details['BankRepayment'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Servicii aditionale</th>
                    <td>
                        <select name="awb[OtherRepayment]">
                            <option value="">Alege o optiune</option>
                            <option value="retur" <?= $awb_details['OtherRepayment'] == 'retur' ? 'selected="selected"' : ''; ?>>Retur</option>
                            <option value="confirmare de primire" <?= $awb_details['OtherRepayment'] == 'confirmare de primire' ? 'selected="selected"' : ''; ?>>Confirmare de primire</option>
                            <option value="colet la schimb" <?= $awb_details['OtherRepayment'] == 'colet la schimb' ? 'selected="selected"' : ''; ?>>Colet la schimb</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Platitor transport</th>
                    <td>
                        <select name="awb[ShipmentPayer]">
                            <option value="1" <?= esc_attr(get_option('uc_plata_transport')) == '1' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="2" <?= esc_attr(get_option('uc_plata_transport')) == '2' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Plata comision ramburs</th>
                    <td>
                        <select name="awb[ShippingRepayment]">
                            <option value="1" <?= esc_attr(get_option('uc_plata_ramburs')) == '1' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="2" <?= esc_attr(get_option('uc_plata_ramburs')) == '2' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Deschidere la livrare</th>
                    <td>
                        <select name="awb[OpenPackage]">
                            <option value="0" <?= esc_attr(get_option('uc_deschidere')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="1" <?= esc_attr(get_option('uc_deschidere')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Livrare sambata</th>
                    <td>
                        <select name="awb[SaturdayDelivery]">
                            <option value="0" <?= esc_attr(get_option('uc_sambata')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="1" <?= esc_attr(get_option('uc_sambata')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Livrare dimineata</th>
                    <td>
                        <select name="awb[MorningDelivery]">
                            <option value="0" <?= esc_attr(get_option('uc_matinal')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="1" <?= esc_attr(get_option('uc_matinal')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Observatii</th>
                    <td><input type="text" name="awb[Observations]" value="<?= $awb_details['Observations'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Continut</th>
                    <td><input type="text" name="awb[PackageContent]" value="<?= $awb_details['PackageContent'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Referinta Serie Client</th>
                    <td><input type="text" name="awb[CustomString]" value="<?= $awb_details['CustomString'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Referinta expeditor 1</th>
                    <td><input type="text" name="awb[SenderReference1]" value="<?= $awb_details['SenderReference1'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Referinta destinatar 1</th>
                    <td><input type="text" name="awb[RecipientReference1]" value="<?= $awb_details['RecipientReference1'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Referinta destinatar 2</th>
                    <td><input type="text" name="awb[RecipientReference2]" value="<?= $awb_details['RecipientReference2'];?>"></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Referinta facturare</th>
                    <td><input type="text" name="awb[InvoiceReference]" value="<?= $awb_details['InvoiceReference'];?>"></td>
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

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="awb[DeliveryPudoPoint]"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery($ => {
    $('form select[name="awb[DeliveryPudoPoint]"]').selectWoo({ placeholder: "Alege un punct Ship & Go", allowClear: true });
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});

    function template_row_fields(row_index){
        return `
            <tr>
                <td>
                    <input type="hidden" name="awb[ParcelCodes][${row_index}][Code]" value="${row_index}">
                    <input type="hidden" name="awb[ParcelCodes][${row_index}][Type]" value="1">
                    <input type="text" name="awb[ParcelCodes][${row_index}][Length]" value="" required>
                </td>
                <td>
                    <input type="text" name="awb[ParcelCodes][${row_index}][Width]" value="" required>
                </td>
                <td>
                    <input type="text" name="awb[ParcelCodes][${row_index}][Height]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[ParcelCodes][${row_index}][Weight]" value="" required>
                </td>
            </tr>
        `;
    }

    $('input[name="awb[Parcels]"]').change(function (){
        let parcels = $(this).val(),
            current_rows = $('.urgent_parcel_size_table tr').length - 1;

        if (parcels < 1) {
            $('.urgent_parcel_size_table tr').first().hide();
        } else {
            $('.urgent_parcel_size_table tr').first().show();
        }

        if (current_rows > parcels) {
            $('.urgent_parcel_size_table tr').slice(parcels-current_rows).remove();
        }

        while (current_rows < parcels) {
            $('.urgent_parcel_size_table').append(template_row_fields(current_rows));
            current_rows++;
        }
    });
});
</script>
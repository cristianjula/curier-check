<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$additional_services = get_option('memex_additional_services', []) ?: [];
?>


<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('memex', 'generate', $order_id) ?>">

        <input type="hidden" name="awb[shipmentRequest][ShipFrom][PointId]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['PointId']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipTo][PointId]" value="<?= $awb_details['shipmentRequest']['ShipTo']['PointId']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][Name]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['Name']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][Person]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['Person']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][Address]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['Address']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][City]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['City']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][PostCode]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['PostCode']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][CountryCode]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['CountryCode']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][Contact]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['Contact']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][Email]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['Email']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][ShipFrom][IsPrivatePerson]" value="<?= $awb_details['shipmentRequest']['ShipFrom']['IsPrivatePerson']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][MPK]" value="<?= $awb_details['shipmentRequest']['MPK']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][RebateCoupon]" value="<?= $awb_details['shipmentRequest']['RebateCoupon']; ?>" />
        <input type="hidden" name="awb[shipmentRequest][LabelFormat]" value="<?= $awb_details['shipmentRequest']['LabelFormat']; ?>" />

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
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][Name]" value="<?= $awb_details['shipmentRequest']['ShipTo']['Name']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][Address]" value="<?= $awb_details['shipmentRequest']['ShipTo']['Address']; ?>"></td>
                </tr>

                <tr style="display: none;">
                    <th scope="row">Codul tarii:</th>
                    <td><input type="hidden" name="awb[shipmentRequest][ShipTo][CountryCode]" value="<?= $awb_details['shipmentRequest']['ShipTo']['CountryCode']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Oras:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][City]" value="<?= $awb_details['shipmentRequest']['ShipTo']['City']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][PostCode]" value="<?= $awb_details['shipmentRequest']['ShipTo']['PostCode']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana contact:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][Person]" value="<?= $awb_details['shipmentRequest']['ShipTo']['Person']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][Contact]" value="<?= $awb_details['shipmentRequest']['ShipTo']['Contact']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[shipmentRequest][ShipTo][Email]" value="<?= $awb_details['shipmentRequest']['ShipTo']['Email']; ?>"></td>
                </tr>

                <tr>
                    <th scope="row">Persoana privata:</th>
                    <td>
                        <select name="awb[shipmentRequest][ShipTo][IsPrivatePerson]">
                            <option value="true" <?= $awb_details['shipmentRequest']['ShipTo']['IsPrivatePerson'] == true ? 'selected' : '' ?>>Da</option>
                            <option value="false" <?= $awb_details['shipmentRequest']['ShipTo']['IsPrivatePerson'] == false ? 'selected' : '' ?>>Nu</option>
                        </select>
                    </td>
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
                    <th scope="row">Serviciu:</th>
                    <td>
                        <select name="awb[shipmentRequest][ServiceId]">
                            <option value="38" <?= esc_attr(get_option('memex_service_id')) == '38' ? 'selected="selected"' : ''; ?>>38 - National Standard</option>
                            <option value="112" <?= esc_attr(get_option('memex_service_id')) == '112' ? 'selected="selected"' : ''; ?>>112 - Express 6 ore Bucuresti</option>
                            <option value="113" <?= esc_attr(get_option('memex_service_id')) == '113' ? 'selected="selected"' : ''; ?>>113 - Colete Grele</option>
                            <option value="121" <?= esc_attr(get_option('memex_service_id')) == '121' ? 'selected="selected"' : ''; ?>>121 - Loco Standard</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Servicii aditionale :</th>
                    <td>
                        <select name="awb[memex_additional_services][]" multiple>
                            <option value="SSMS" <?= in_array('SSMS', $additional_services) ? 'selected' : '' ?>>Serviciu SMS</option>
                            <option value="TMP" <?= in_array('TMP', $additional_services) ? 'selected' : '' ?>>Serviciu deschidere la livrare</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Continut:</th>
                    <td><input type="text" name="awb[shipmentRequest][ContentDescription]" value="<?= $awb_details['shipmentRequest']['ContentDescription']; ?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Colete</th>
                    <td><input type="number" min="0" name="awb[Parcels]" value="<?= esc_attr(get_option('memex_package_count')); ?>"></td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:1rem 1.5rem;">
                        <table class="widefat memex_package_size_table">
                            <tbody>
                                <tr <?php if (get_option('memex_package_count') < 1) echo 'style="display: none;"'; ?>>
                                    <th scope="col">Lungime (cm)</th>
                                    <th scope="col">Latime (cm)</th>
                                    <th scope="col">Inaltime (cm)</th>
                                    <th scope="col">Greutate (Kg)</th>
                                </tr>
                                <?php
                                for ($i = 0; $i < get_option('memex_package_count'); $i++) {
                                    $row_field_length = ($i == 0 ? $awb_details['shipmentRequest']['Parcels'][0]['Parcel']['D'] : null);
                                    $row_field_width = ($i == 0 ? $awb_details['shipmentRequest']['Parcels'][0]['Parcel']['S'] : null);
                                    $row_field_height = ($i == 0 ? $awb_details['shipmentRequest']['Parcels'][0]['Parcel']['W'] : null);
                                    $row_field_weight = ($i == 0 ? $awb_details['shipmentRequest']['Parcels'][0]['Parcel']['Weight'] : null);
                                ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][IsNST]" value="true">
                                            <input type="hidden" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][Type]" value="Package">
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][D]" value="<?= $row_field_length; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][S]" value="<?= $row_field_width; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][W]" value="<?= $row_field_height; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][Weight]" value="<?= $row_field_weight; ?>" required>
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
                    <th scope="row">Plicuri</th>
                    <td><input type="number" min="0" name="awb[Envelopes]" value="<?= esc_attr(get_option('memex_envelope_count')); ?>"></td>
                </tr>

                <tr>
                    <th></th>
                    <td>
                        <table cellspacing="0" class="memex_envelope_size_table">
                            <tbody>
                                <tr <?php if (get_option('memex_envelope_count') < 1) echo 'style="display: none;"'; ?>>
                                    <th scope="col">Lungime (cm)</th>
                                    <th scope="col">Latime (cm)</th>
                                    <th scope="col">Inaltime (cm)</th>
                                    <th scope="col">Greutate (Kg)</th>
                                </tr>
                                <?php
                                for ($i = get_option('memex_package_count'); $i < (int)get_option('memex_envelope_count') + (int)get_option('memex_package_count'); $i++) {
                                    $index = 1;
                                    if (get_option('memex_package_count') == 0) {
                                        $index = 0;
                                    }
                                    $row_field_length = ($i == get_option('memex_package_count') ? $awb_details['shipmentRequest']['Parcels'][$index]['Parcel']['D'] : null);
                                    $row_field_width = ($i == get_option('memex_package_count') ? $awb_details['shipmentRequest']['Parcels'][$index]['Parcel']['S'] : null);
                                    $row_field_height = ($i == get_option('memex_package_count') ? $awb_details['shipmentRequest']['Parcels'][$index]['Parcel']['W'] : null);
                                    $row_field_weight = ($i == get_option('memex_package_count') ? $awb_details['shipmentRequest']['Parcels'][$index]['Parcel']['Weight'] : null);
                                ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][IsNST]" value="true">
                                            <input type="hidden" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][Type]" value="Envelope">
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][D]" value="<?= $row_field_length; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][S]" value="<?= $row_field_width; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][W]" value="<?= $row_field_height; ?>" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.1" max="0.5" name="awb[shipmentRequest][Parcels][<?= $i ?>][Parcel][Weight]" value="<?= $row_field_weight; ?>" required>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <th align="left">Valoare ramburs:</th>
                    <td><input type="number" step="0.01" name="awb[shipmentRequest][COD][Amount]" value="<?= $awb_details['shipmentRequest']['COD']['Amount']; ?>"></td>
                </tr>

                <tr>
                    <th align="left">Valoare asigurare:</th>
                    <td><input type="number" step="0.01" name="awb[shipmentRequest][InsuranceAmount]" value="<?= $awb_details['shipmentRequest']['InsuranceAmount']; ?>"></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" defer></script>
<script>
    jQuery($ => {
        $('form select').select2();
        $("input[type=submit]").on("click", function() {
            $(this).addClass("disabled"), $(this).val("Se generează AWB..."), setTimeout(() => {
                $(this).removeClass("disabled"), $(this).val("Generează AWB")
            }, 5e3)
        });

        function template_row_fields(row_index, type) {
            let td;
            if (type == 'Package') {
                td = `<input type="number" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][Weight]" value="" required>`;
            } else {
                td = `<input type="number" step="0.1" max="0.5" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][Weight]" value="" required>`;
            }
            return `
            <tr>
                <td>
                    <input type="hidden" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][IsNST]" value="true">
                    <input type="hidden" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][Type]" value="${ type }">
                    <input type="number" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][D]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][S]" value="" required>
                </td>
                <td>
                    <input type="number" name="awb[shipmentRequest][Parcels][${ row_index }][Parcel][W]" value="" required>
                </td>
                <td>` +
                td +
                `</td>
            </tr>
        `;
        }

        function create(count, current_rows, table_tr, table, type) {
            if (count < 1) {
                table_tr.first().hide();
            } else {
                table_tr.first().show();
            }

            if (current_rows > count) {
                table_tr.slice(count - current_rows).remove();
            }

            let index = 0;
            while (current_rows < count) {
                index = $('.memex_package_size_table tr').length + $('.memex_envelope_size_table tr').length - 2;
                table.find('tbody').append(template_row_fields(index, type));
                current_rows++;
            }
        }

        $('input[name="awb[Parcels]"]').change(function() {
            create(
                $(this).val(),
                $('.memex_package_size_table tr').length - 1,
                $('.memex_package_size_table tr'),
                $('.memex_package_size_table'),
                'Package'
            )
        });

        $('input[name="awb[Envelopes]"]').change(function() {
            create(
                parseInt($(this).val()),
                $('.memex_envelope_size_table tr').length - 1,
                $('.memex_envelope_size_table tr'),
                $('.memex_envelope_size_table'),
                'Envelope'
            )
        });

    });
</script>
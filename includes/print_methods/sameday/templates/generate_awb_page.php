<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$sameday = CurieRO()->container->get(CurieroSamedayClass::class);
$samedayAPI = CurieRO()->container->get(APISamedayClass::class);
$services = collect();

$order = curiero_get_order($order_id);
$lockerLastMile = $order->get_meta('curiero_sameday_lockers', true);
$lockers = collect($sameday->getLockers());
?>

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>

    <?php if ($lockerLastMile) : ?>
    <div class="notice notice-info locker-info">
        <h4>A fost selectata optiunea de EasyBox pentru aceasta comanda. Recomandam selectarea unui serviciu compatibil.</h4>
    </div>
    <?php endif; ?>
    <br>

    <form method="POST" action="<?= curiero_order_action_url('sameday', 'generate', $order_id) ?>">
        <input type="hidden" name="awb[reference]" value="<?= $awb_details['reference'];?>">
        <input type="hidden" name="awb[lockerFirstMile]" value="<?= $awb_details['lockerFirstMile'];?>">

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
                    <td><input type="text" name="awb[state]" value="<?= $awb_details['state'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Oras:</th>
                    <td><input type="text" name="awb[city]" value="<?= $awb_details['city'];?>"></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[address]" value="<?= $awb_details['address'];?>"></td>
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
                    <th align="left">Punct de ridicare:</th>
                    <td>
                        <select name="awb[pickup_point]">
                            <?php
                                $pickup_points = get_transient('curiero_sameday_pickup_points');
                                $current_pickup_point = $awb_details['pickup_point'];
                                if (empty($pickup_points)) {
                                    $pickup_points = json_decode($sameday->callMethod('pickup_points', [], 'GET')['message'], true);
                                }
                                if (!empty($pickup_points)) {
                                    set_transient('curiero_sameday_pickup_points', $pickup_points, DAY_IN_SECONDS);
                                    foreach($pickup_points as $pickup_point) {
                                        $selected = ($pickup_point['id'] == $current_pickup_point) ? 'selected="selected"' : '';
                                        echo "<option value='{$pickup_point['id']}' {$selected}>{$pickup_point['name']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Tip trimitere:</th>
                    <td>
                        <select name="awb[package_type]">
                            <option value="0" <?= $awb_details['package_type'] == '0' ? 'selected="selected"' : ''; ?>>Colet</option>
                            <option value="1" <?= $awb_details['package_type'] == '1' ? 'selected="selected"' : ''; ?>>Plic</option>
                            <option value="2" <?= $awb_details['package_type'] == '2' ? 'selected="selected"' : ''; ?>>Palet</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Serviciu:</th>
                    <td>
                        <select name="awb[service_id]">
                            <?php
                                if ((bool) get_option('sameday_valid_auth')) {
                                    $services = $samedayAPI->getAdditionalServices();
                                    $current_service_id = $lockerLastMile ? esc_attr(get_option('sameday_locker_service_id')) : esc_attr(get_option('sameday_ord_service_id'));
                                    if ($services->isNotEmpty()) {
                                        if(!is_array($services->first()) || !isset($services->first()['delivery'])) {
                                            delete_transient('curiero_sameday_services');
                                            $services = $samedayAPI->getAdditionalServices();
                                        }
                                        foreach($services->sortByDesc('delivery.name') as $service)
                                            echo "<option ". selected($service['id'], $current_service_id, false) ." value='{$service['id']}'>{$service['delivery']['name']} - {$service['name']}</option>";
                                    }
                                }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Servicii aditionale:</th>
                    <td>
                        <select name="awb[serviceTaxIds][]" multiple></select>
                    </td>
                </tr>

                <tr valign="top" id="sameday_easybox" style="<?= empty($lockerLastMile) ? 'display: none;' : '' ?>">
                    <th scope="row">Punct ridicare Easybox:</th>
                    <td>
                        <select name="awb[lockerLastMile]" id="awb[lockerLastMile]" style="width: 100%;">
                            <option></option>
                            <?php
                            foreach ($lockers as $locker) : ?>
                                <option <?php selected($locker['id'], $awb_details['lockerLastMile'] ?? null, true) ?> value="<?= esc_html($locker['id'])?>"> <?= ucwords(strtolower($locker['name'])) . ' - ' . ucwords(strtolower($locker['address']))  ?> </option>
                            <?php endforeach;
                            ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Numar colete:</th>
                    <td><input type="number" min="1" name="parcel_number" value="1"></td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:1rem 1.5rem;">
                        <table class="widefat sameday_parcel_table">
                            <tbody>
                                <tr>
                                    <th scope="col">Lungime (cm)</th>
                                    <th scope="col">Latime (cm)</th>
                                    <th scope="col">Inaltime (cm)</th>
                                    <th scope="col">Greutate (Kg)</th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="text" name="awb[parcel_dimensions][0][length]" value="<?= $awb_details['length'] ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="awb[parcel_dimensions][0][width]" value="<?= $awb_details['width'] ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="awb[parcel_dimensions][0][height]" value="<?= $awb_details['height'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="awb[parcel_dimensions][0][weight]" value="<?= $awb_details['weight'] ?>" required>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <th scope="row">Observatii (max 200 caractere):</th>
                    <td>
                        <textarea name="awb[observation]" lines="2" maxlength="200"><?= $awb_details['observation'];?></textarea>
                        <sub style="float:right;"><span class="letterCount">0</span>/200</sub>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Continut:</th>
                    <td><input type="text" name="awb[priceObservation]" value="<?= $awb_details['priceObservation'] ?>"></td>
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

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="awb[lockerLastMile]"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery($ => {
    $('form select[name="awb[lockerLastMile]"]').select2({ placeholder: "Alege un Easybox", allowClear: true });
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});

    function template_row_fields(row_index){
        return `
            <tr>
                <td>
                    <input type="text" name="awb[parcel_dimensions][${row_index}][length]" value="">
                </td>
                <td>
                    <input type="text" name="awb[parcel_dimensions][${row_index}][width]" value="">
                </td>
                <td>
                    <input type="text" name="awb[parcel_dimensions][${row_index}][height]" value="">
                </td>
                <td>
                    <input type="number" name="awb[parcel_dimensions][${row_index}][weight]" value="" required>
                </td>
            </tr>
        `;
    }

    $('input[name="parcel_number"]').change(function (){
        let parcels = $(this).val(),
            current_rows = $('.sameday_parcel_table tr').length - 1;

        if (current_rows > parcels) {
            $('.sameday_parcel_table tr').slice(parcels-current_rows).remove();
        }

        while (current_rows < parcels) {
            $('.sameday_parcel_table').append(template_row_fields(current_rows));
            current_rows++;
        }
    })

    $('.letterCount').text($('textarea[name="awb[observation]"]').val().length);
    $('textarea[name="awb[observation]"]').on('keyup change', function(){$('.letterCount').text($(this).val().length);})

    const service_list = <?= $services->isNotEmpty() ? $services : collect() ?>;
    const lockers_list = <?= $lockers->isNotEmpty() ? $lockers : collect() ?>;
    const selected_locker_id = "<?= $lockerLastMile ?>";

    const set_additional_services = function ()
    {
        $('select[name="awb[serviceTaxIds][]"] option').remove();
        $('select[name="awb[serviceTaxIds][]"]').val('');
        const current_package_type = $('select[name="awb[package_type]"]').val(),
            current_service_id =  $('select[name="awb[service_id]"]').val(),
            current_service = service_list.find(service => service['id'] == current_service_id) || {},
            available_additional_services = Object.keys(current_service).length ? current_service['optional_taxes'].filter(tax => tax['package_type'] == current_package_type) : [];
        available_additional_services.forEach(service => {
            const new_option = new Option(service['name'], service['id']);
            $('select[name="awb[serviceTaxIds][]"]').append(new_option);
        });
        $('select[name="awb[serviceTaxIds][]"]').trigger('change');
    }

    const set_existing_services = function ()
    {
        const service_has_locker = jQuery('select[name="awb[service_id]"] option:selected').text().includes('Locker');
        const service_ids = service_has_locker
            ? (JSON.parse('<?= json_encode(get_option('sameday_locker_additional_services', [])) ?>') || [])
            : (JSON.parse('<?= json_encode(get_option('sameday_ord_additional_services', [])) ?>') || []);
        if (typeof service_ids !== typeof []) return;
        service_ids.forEach(id => $('select[name="awb[serviceTaxIds][]"] option[value='+id+']').prop('selected', true))
        $('select[name="awb[serviceTaxIds][]"]').trigger('change');
    }

    const show_or_hide_easybox = function ()
    {
        const service_has_locker = jQuery('select[name="awb[service_id]"] option:selected').text().includes('Locker');
        if (service_has_locker) {
            $('#sameday_easybox').show();
            $('select[name="awb[lockerLastMile]"]').val(selected_locker_id).trigger('change');
        } else {
            $('#sameday_easybox').hide();
            $('select[name="awb[lockerLastMile]"]').val('').trigger('change');
        }
    }

    const filter_lockers = function (location, type)
    {
        let lockers = [];

        if(type == 'city') {
            lockers = lockers_list.filter(locker => locker['city'] === location);
        }

        if(lockers.length == 0 || type == 'state') {
            let state = document.querySelector('input[name="awb[state]"]')?.value;
            lockers = lockers_list.filter(locker => locker['county'] === state);
        }

        $('select[name="awb[lockerLastMile]"] option').remove().end();
        $('select[name="awb[lockerLastMile]"]').append(new Option('', ''));

        lockers.forEach(
            locker => $('select[name="awb[lockerLastMile]"]').append(new Option(locker['city'] + ' ' + locker['name'], locker['id']))
        );
        $('select[name="awb[lockerLastMile]"]').trigger('change');
    }

    $('select[name="awb[package_type]"]').on('change', () => set_additional_services());
    $('select[name="awb[service_id]"]').on('change', () => { set_additional_services(); set_existing_services(); show_or_hide_easybox()});

    $('select[name="awb[package_type]"]').trigger('change');
    $('select[name="awb[service_id]"]').trigger('change');

    $('input[name="awb[state]"]').change((e) => filter_lockers(e.currentTarget.value, 'state'));
    $('input[name="awb[city]"]').change((e) => filter_lockers(e.currentTarget.value, 'city'));
});
</script>

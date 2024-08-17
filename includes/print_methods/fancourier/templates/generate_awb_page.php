<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

function format_field_labels($k)
{
    switch ($k) {
        case 'height': return 'Inaltime';
        case 'length': return 'Lungime';
        case 'width': return 'Latime';
        case 'personal_data': return 'Date personale';
        case 'val_decl': return 'Valoare asigurata';
        default: return ucfirst(str_replace('_', ' ', $k));
    }
}

$countiesList = curiero_get_counties_list();

$courier = CurieRO()->container->get(APIFanCourierClass::class);
$servicesListFan = $courier->getServices() ?: [];
$clientIds = $courier->getClientIds() ?: [];
$fanbox_list = CurieRO()->container->get(CurieroFanClass::class)->getFanboxList() ?: [];

$isOldFanboxId = substr($awb_details['fanbox_id'], 0, 3) === 'FAN';
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>

    <?php if (!empty($awb_details['fanbox_id'])) { ?>
    <div class="notice notice-info locker-info">
        <h4>A fost selectata optiunea de FANBox pentru aceasta comanda. Recomandam selectarea unui serviciu compatibil.</h4>
    </div>
    <?php } ?>
    <br>

    <form method="POST" action="<?= curiero_order_action_url('fancourier', 'generate', $order_id); ?>">
        <input type="hidden" name="awb[epod_opod]" value="<?= $awb_details['epod_opod']; ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Optiuni</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
        <?php
            unset($awb_details['epod_opod']);
foreach ($awb_details as $k => $v) {
    if (is_array($v)) {
        $label = $k;
        ?>
            <tr valign="top">
                <th scope="row"><?= $label; ?></th>
                <td></td>
            </tr>
            <?php foreach ($v as $k => $v) { ?>
                <?php if ($k == 'LocationId') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $label; ?>][<?= $k; ?>]">
                            <?php foreach ($jsonPickupLocations as $c) { ?>
                            <option value="<?= $c['LocationId']; ?>"<?php if ($v == $c['LocationId']) { ?> selected<?php } ?>><?= $c['Name']; ?> <?= $c['ContactPerson']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } else { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td><input type="text" name="awb[<?= $label; ?>][<?= $k; ?>]" value="<?= $v; ?>" /></td>
                </tr>
                <?php } ?>
            <?php }
            } else {
                if ($k == 'judet') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <?php foreach ($countiesList as $kk => $c) { ?>
                            <option value="<?= $c; ?>"<?php if ($v == $kk || strtolower($v) == strtolower($c)) { ?> selected<?php } ?>><?= $c; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } elseif ($k == 'tip_serviciu') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <?php foreach ($servicesListFan as $c) { ?>
                            <option value="<?= $c; ?>"<?php if ($v == $c || strtolower($v) == strtolower($c)) { ?> selected<?php } ?>><?= $c; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <?php } elseif ($k == 'fan_id') { ?>
                    <tr valign="top">
                        <th scope="row">Punct de ridicare:</th>
                        <td>
                            <select name="awb[fan_id]">
                                <?php foreach ($clientIds as $list_clientId) { ?>
                                    <option value="<?= $list_clientId['id'] ?? null; ?>"
                                        <?php if (get_option('fan_clientID') == $list_clientId['id']) { ?> selected <?php } ?>>
                                        <?= $list_clientId['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                <?php } elseif ($k == 'fanbox_id') { ?>
                <tr valign="top">
                    <th scope="row">Adresa FANBox:</th>
                    <td>
                        <?php if ($isOldFanboxId):?>
                        <input type="text" name="awb[<?= $k; ?>]" value="<?= $v; ?>" size="40"; />
                        <?php else: ?>
                        <select name="awb[<?= $k; ?>]">
                            <option value=""></option>
                            <?php foreach ($fanbox_list as $fanbox) { ?>
                            <option <?= selected($fanbox['id'], $awb_details['fanbox_id'] ?? null, true); ?> value="<?= esc_html($fanbox['id']); ?>">
                                <?= $fanbox['name']?> - <?= $fanbox['address'] ?>, <?= $fanbox['locality'] ?>
                            </option>
                            <?php } ?>
                        </select>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } elseif ($k == 'deschidere_la_livrare') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <option <?= selected($v, 'A', false); ?> value="A">Da</option>
                            <option <?= selected($v, '', false); ?> value="">Nu</option>
                        </select>
                    </td>
                </tr>
                <?php } elseif ($k == 'livrare_sambata') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <option <?= selected($v, 'S', false); ?> value="S">Da</option>
                            <option <?= selected($v, '', false); ?> value="">Nu</option>
                        </select>
                    </td>
                </tr>
                <?php } elseif ($k == 'plata_expeditie') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <option <?= selected($v, 'destinatar', false); ?> value="destinatar">Destinatar</option>
                            <option <?= selected($v, 'expeditor', false); ?> value="expeditor">Expeditor</option>
                        </select>
                    </td>
                </tr>
                <?php } elseif ($k == 'plata_ramburs') { ?>
                <tr valign="top">
                    <th scope="row"><?= format_field_labels($k); ?>:</th>
                    <td>
                        <select name="awb[<?= $k; ?>]">
                            <option <?= selected($v, 'destinatar', false); ?> value="destinatar">Destinatar</option>
                            <option <?= selected($v, 'expeditor', false); ?> value="expeditor">Expeditor</option>
                        </select>
                    </td>
                </tr>
                <?php } else { ?>
                    <tr valign="top">
                        <th scope="row"><?= format_field_labels($k); ?>:</th>
                        <td><input type="text" name="awb[<?= $k; ?>]" value="<?= $v; ?>" size="40"; /></td>
                    </tr>
                <?php } ?>
                <?php } ?>
            <?php } ?>
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

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="awb[fanbox_id]"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
jQuery(function($){
    $('form select[name="awb[fanbox_id]"]').select2({ placeholder: "Alege un FANBox", allowClear: true });
    $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});
})
</script>
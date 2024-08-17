<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>

<tr class="wc_shipping_mygls_box_header shipping" style="border-bottom: 0;">
    <th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct GLS Box', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_mygls_box shipping">
    <td colspan="2">
        <style scoped>
            .woocommerce-checkout-review-order-table tfoot .wc_shipping_mygls_box td {
                padding-bottom: 25px;
            }
        </style>
        <?php
        if ($mygls_box_list->isEmpty()) : ?>
            <p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
        <?php
        else : ?>
            <select name="curiero_mygls_box" id="curiero_mygls_lockers_select" style="width: 100%;">
                <option disabled <?= selected(!$current_mygls_box_exists || empty($selected_mygls_box), true, true) ?>>Alege punct GLS Box</option>
                <?php foreach ($mygls_box_list as $mygls_box) : ?>
                    <option <?= selected($mygls_box['id'], $selected_mygls_box['id'] ?? null, true) ?> value="<?= esc_html($mygls_box['id']) ?>">
                        <?= ucwords(strtolower($mygls_box['name'])) ?> - <?= $mygls_box['city'] ?>, <?= ucwords(strtolower(ltrim($mygls_box['address'], '_'))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
            if ($box_map_active == "yes") : ?>
                <button type="button" id="gls-map-button" class=".dugme-gls_shipping_method_parcel_locker" style="padding: 10px; font-size: 15px; width: 100%; margin-top: 15px;"><?php echo __('Arata Harta GLS Box', 'curiero-plugin') ?></button>
            <?php endif; ?>
            <script>
                jQuery($ => {
                    (typeof $().selectWoo == 'function') && $('#curiero_mygls_lockers_select').selectWoo();
                    $(document.body).on('change', '#curiero_mygls_lockers_select', () => delete window.curiero_selected_mygls_lockerId);

                    window.curiero_selected_mygls_lockerId && ($('#curiero_mygls_lockers_select').val() != window.curiero_selected_mygls_lockerId) && $('#curiero_mygls_lockers_select').val(window.curiero_selected_mygls_lockerId).trigger('change');
                });
            </script>
        <?php endif; ?>
    </td>
</tr>
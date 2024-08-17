<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_fan_box_header shipping" style="border-bottom: 0;">
    <th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct FANBox', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_fan_box shipping">
    <td colspan="2">
        <style scoped>
            .woocommerce-checkout-review-order-table tfoot .wc_shipping_fan_box td {
                padding-bottom: 25px;
            }
        </style>
        <?php
        if ($fanbox_list->isEmpty()) : ?>
            <p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
        <?php
        else : ?>
            <select name="curiero_fan_fanbox" id="curiero_fan_lockers_select" style="width: 100%;">
                <option disabled <?= selected(!$current_fanbox_exists || empty($selected_fanbox), true, true) ?>>Alege punct FANBox</option>
                <?php foreach ($fanbox_list as $fanbox) : ?>
                    <option <?= selected($fanbox['id'], $selected_fanbox['id'] ?? null, true) ?> value="<?= esc_html($fanbox['id']) ?>">
                        <?= $fanbox['name'] ?> - <?= $fanbox['address'] ?>, <?= $fanbox['locality'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php
            if ($fanbox_map_active == "yes") : ?>
                <button type="button" id="openMap" style="padding: 10px; font-size: 15px; width: 100%; margin-top: 15px;"><?php echo __('Arata Harta FANBox', 'curiero-plugin') ?></button>

                <span id="mapDiv"></span>
            <?php endif ?>

            <script>
                jQuery($ => {
                    window.curiero_selected_fan_lockerId = window.curiero_selected_fan_lockerId || null;
                    (typeof $().selectWoo == 'function') && $('.wc_shipping_fan_box').find('select[name="curiero_fan_fanbox"]').selectWoo();

                    $(document.body).on('change', '#curiero_fan_lockers_select', () => delete window.curiero_selected_fan_lockerId);
                    window.curiero_selected_fan_lockerId && ($('#curiero_fan_lockers_select').val() != window.curiero_selected_fan_lockerId) && $('#curiero_fan_lockers_select').val(window.curiero_selected_fan_lockerId).trigger('change');
                });
            </script>
        <?php endif; ?>
    </td>
</tr>
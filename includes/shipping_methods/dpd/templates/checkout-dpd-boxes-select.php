<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_dpd_boxes_header shipping" style="border-bottom: 0;">
	<th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct de ridicare', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_dpd_boxes">
	<td colspan="2">
		<style scoped>
			.woocommerce-checkout-review-order-table tfoot .wc_shipping_dpd_boxes td { padding-bottom: 25px; }
		</style>
		<?php
		if ($dpd_boxes->isEmpty()) : ?>
			<p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
			<?php
		else: ?>
		<select name="curiero_dpd_box" id="curiero_dpd_box_select" style="width: 100%;">
			<option disabled <?= selected(!$current_box_exists || empty($current_dpd_box), true, true) ?>>Alege un DPDBox</option>
			<?php
			foreach ($dpd_boxes as $dpd_box) : ?>
				<option <?php selected($dpd_box['id'], $current_dpd_box['id'] ?? null, true) ?> value="<?= esc_html($dpd_box['id']) ?>"> <?=  ucwords(strtolower($dpd_box['address'])). ' - '. $dpd_box['name']. " - Locker ". $dpd_box['id'] ?> </option>
			<?php endforeach;
			?>
		</select>
		<script>
			jQuery($ => {
				(typeof $().selectWoo == 'function') && $('.wc_shipping_dpd_boxes').find('#curiero_dpd_box_select').selectWoo();
			});
		</script>
		<?php endif; ?>
	</td>
</tr>
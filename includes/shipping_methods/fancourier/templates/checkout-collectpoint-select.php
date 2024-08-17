<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_fan_collectpoint_header shipping" style="border-bottom: 0;">
	<th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct CollectPoint', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_fan_collectpoint shipping">
	<td colspan="2">
		<style scoped>
			.woocommerce-checkout-review-order-table tfoot .wc_shipping_fan_collectpoint td {padding-bottom: 25px;}
		</style>
		<?php
		if ($collectpoints->isEmpty()) : ?>
			<p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
			<?php
		else: ?>
		<select name="curiero_fan_collectpoint" style="width: 100%;">
			<option disabled <?= selected(!$current_collectpoint_exists || empty($selected_collectpoint), true, true) ?>>Alege un CollectPoint</option>
			<?php foreach ($collectpoints as $collectpoint) : ?>
				<option <?= selected($collectpoint['id'], $selected_collectpoint['id'] ?? null, true) ?> value="<?= esc_html($collectpoint['id']) ?>">
					<?= ucwords(strtolower($collectpoint['address'])) ?>, <?= $collectpoint['locality'] ?>, <?= $collectpoint['county'] ?>
				</option>
			<?php endforeach; ?>
		</select>
		<script>
			jQuery($ => {
				(typeof $().selectWoo == 'function') && $('.wc_shipping_fan_collectpoint').find('select[name=curiero_fan_collectpoint]').selectWoo();
			});
		</script>
		<?php endif; ?>
	</td>
</tr>
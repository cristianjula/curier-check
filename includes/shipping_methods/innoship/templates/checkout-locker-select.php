<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_innoship_lockers_header shipping" style="border-bottom: 0;">
	<th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege Locker', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_innoship_lockers shipping">
	<td colspan="2">
		<style scoped>
			.woocommerce-checkout-review-order-table tfoot .wc_shipping_innoship_lockers td {padding-bottom: 25px;}
		</style>
		<?php
		if (empty($locker_list)) : ?>
			<p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
			<?php
		else: ?>
		<select name="curiero_innoship_locker" style="width: 100%;">
			<option disabled <?= selected(!$selected_locker_exists || empty($selected_locker), true, true) ?>>Alege Locker</option>
			<?php foreach ($locker_list as $locker) : ?>
				<option <?= selected($locker['id'], $selected_locker['id'] ?? null, true) ?> value="<?= esc_html($locker['id']) ?>">
					<?= $locker['name'] ?> - <?= $locker['addressText'] ?>
				</option>
			<?php endforeach; ?>
		</select>
		<script>
			jQuery($ => {
				(typeof $().selectWoo == 'function') && $('.wc_shipping_innoship_lockers').find('select[name="curiero_innoship_locker"]').selectWoo();
			});
		</script>
		<?php endif; ?>
	</td>
</tr>
<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_cargus_lockers_header shipping" style="border-bottom: 0;">
	<th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct Ship & Go', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_cargus_lockers shipping">
	<td colspan="2">
		<style scoped>
			.woocommerce-checkout-review-order-table tfoot .wc_shipping_cargus_lockers td {padding-bottom: 25px;}
			.woocommerce-checkout-review-order-table {table-layout: fixed;}
			.select2-selection--single {overflow: hidden; text-overflow: ellipsis; white-space: pre-line;}
		</style>
		<?php if ($lockers->isEmpty()) : ?>
			<p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
		<?php else : ?>
		<div>
			<select name="curiero_cargus_locker" id="curiero_cargus_locker_select" style="width: 100%;">
				<option disabled <?= selected(!$current_locker_exists || empty($current_locker), true, true) ?>>Alege un punct Ship & Go</option>
				<?php foreach ($lockers as $locker) : ?>
					<option <?php selected($locker['Id'], $current_locker['Id'] ?? null, true) ?> value="<?= esc_html($locker['Id']) ?>">
						<?php echo ucwords(strtolower($locker['City'])) . ' - ' . $locker['Name'];
						?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<script>
			jQuery($ => {
				(typeof $().selectWoo == 'function') && $('#curiero_cargus_locker_select').selectWoo();
			});
		</script>
		<?php endif; ?>
	</td>
</tr>
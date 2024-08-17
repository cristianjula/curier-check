<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<tr class="wc_shipping_sameday_lockers_header shipping" style="border-bottom: 0;">
	<th colspan="2" style="padding-bottom:0"><b><?= esc_html_e('Alege punct EasyBox', 'curiero-plugin') ?> <abbr class="required" title="required">*</abbr></b></th>
</tr>
<tr class="wc_shipping_sameday_lockers shipping">
	<td colspan="2">
		<style scoped>
			.woocommerce-checkout-review-order-table tfoot .wc_shipping_sameday_lockers td {padding-bottom: 25px;}
			.woocommerce-checkout-review-order-table {table-layout: fixed;}
			.select2-selection--single {overflow: hidden; text-overflow: ellipsis; white-space: pre-line;}
		</style>
		<div>
			<?php
			if ($lockers->isEmpty()) : ?>
				<p style="margin: 0;"><?= esc_html_e('Nu exista puncte de livrare disponibile pentru adresa selectata.', 'curiero-plugin') ?></p>
				<?php
			else: ?>
			<select name="curiero_sameday_lockers" id="curiero_sameday_lockers_select" style="width: 100%;">
				<option disabled <?= selected(!$current_locker_exists || empty($current_locker), true, true) ?>>Alege un EasyBox</option>
				<?php foreach ($lockers as $locker) : ?>
					<option <?php selected($locker['id'], $current_locker['id'] ?? null, true) ?> value="<?= esc_html($locker['id']) ?>">
						<?php if ($local_box_found) :
							echo ucwords(strtolower($locker['name'])) . ' - ' . ucwords(strtolower($locker['address']));
						else :
							echo ucwords(strtolower($locker['city'])) . ' - ' . ucwords(strtolower($locker['name'])) . ' - ' . ucwords(strtolower($locker['address']));
						endif; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php if ($lockers_map_active == "yes") : ?>
			<div> 
				<button type="button" 
				id="select_locker_map" 
				class="button alt sameday_select_locker" 
				style="padding: 10px; font-size: 15px; width: 100%; margin-top: 15px;">
				<?php echo __('Arata Harta Easybox', 'curiero-plugin') ?></button> 
			</div>
		<?php endif; ?>
		<script>
			jQuery($ => {
				(typeof $().selectWoo == 'function') && $('#curiero_sameday_lockers_select').selectWoo();
				$(document.body).on('change', '#curiero_sameday_lockers_select', () => delete window.curiero_selected_sameday_lockerId);
				window.curiero_selected_sameday_lockerId && ($('#curiero_sameday_lockers_select').val() != window.curiero_selected_sameday_lockerId) && $('#curiero_sameday_lockers_select').val(window.curiero_selected_sameday_lockerId).trigger('change');
			});
		</script>
		<?php endif; ?>
	</td>
</tr>
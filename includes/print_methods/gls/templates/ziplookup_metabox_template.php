<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!empty($order)) { ?>
	<div id="generate_awb" class="row bootstrap">
		<?php
        $currentPostCode = $order->get_shipping_postcode() ?: $order->get_billing_postcode();
    if (!empty($currentPostCode)) {
        global $wpdb;
        $cityFound = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}curiero_zipcodes WHERE ZipCode={$currentPostCode} LIMIT 1");
    }
    ?>
		<div class="col-lg-12 well">
			<div class="col-lg-6">
				<?php if (!empty($cityFound)) { ?>
					<?php $currentStreet = $cityFound->Street != ' ' ? ' (' . $cityFound->Street . ')' : ''; ?>
					<strong><?= $currentPostCode; ?></strong> - <?= $cityFound->City . $currentStreet . ' - ' . $cityFound->County; ?><br>
				<?php } else { ?>
					<div class="module_error alert alert-danger" style="margin: 15px 0">
						<strong>Codul poștal indicat in comanda este incorect sau inexistent!</strong>
					</div>
				<?php } ?>
				<hr>
				<div class="form-controller">
					<label>Caută alt cod poștal: </label>
					<div>
						<input class="col-lg-12" type="text" id="zip_keyword" name="zip_keyword" placeholder="Format: Localitate, Strada" style="width: 100%">
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div id="add_new_zip"></div>
			</div>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(document).on("keyup", "#zip_keyword", function() {
			var keyword = jQuery(this).val();
			jQuery("#zip_keyword").autocomplete({
				position: {
					my: "left top+15px",
					at: "left top+15px"
				},
				source: function(request, response) {
					jQuery.ajax({
						dataType: "json",
						type: "POST",
						url: ajaxurl,
						data: {
							keyword: keyword,
							action: 'curiero_fetch_zipcode'
						},
						success: function(data) {
							response(jQuery.map(data.data, function(item) {
								var street = "";
								if (item.street != null && item.street != " ") {
									street = " - " + item.street;
								}
								return {
									label: item.zip_code + " - " + item.city + street + " - " + item.county,
									zip_code: item.zip_code,
									city: item.city,
									street: item.street || '',
									county: item.county,
								};
							}));
						}
					});
				},
				minLength: 3,
				select: function(event, ui) {
					jQuery("#add_new_zip").html("<div><input type='hidden' name='zip_code_val' value='" + ui.item.zip_code + "'><br><input type='submit' name='UpdateZipOrder' value='Actualizeaza in comanda' class='btn btn-primary add_note button' style='width:100%'></div>");
				}
			});
		});
	</script>
<?php } ?>
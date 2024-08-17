<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h2>CurieRO - Genereaza AWB <?= $courier_name ?></h2>
    <br>
    <form method="POST" action="<?= curiero_order_action_url('gls', 'generate', $order_id) ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Expeditor</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr valign="top">
                    <th scope="row">ID Expeditor:</th>
                    <td><input type="text" name="awb[senderid]" value="<?=$awb_details['senderid']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Precompleteaza alt expeditor:</th>
                    <td>
                        <?php
                        $other_senders = maybe_unserialize(get_option('GLS_other_senders')) ?: [];

                        if(!empty($other_senders)) : ?>
                            <select class="other_sender" name="other_sender">
                                <option value="other_sender"
                                    data-name="<?= $awb_details['sender_name'] ?? null; ?>"
                                    data-address="<?= $awb_details['sender_address'] ?? null; ?>"
                                    data-city="<?= $awb_details['sender_city'] ?? null; ?>"
                                    data-zipcode="<?= $awb_details['sender_zipcode'] ?? null; ?>"
                                    data-phone="<?= $awb_details['sender_phone'] ?? null; ?>"
                                    data-email="<?= $awb_details['sender_email'] ?? null; ?>"
                                    data-contact="<?= $awb_details['contact'] ?? null; ?>">
                                Expeditor implicit
                                </option>
                                <?php foreach($other_senders as $other_sender):?>
                                    <option value="other_sender"
                                            data-name="<?= $other_sender['name'] ?? null; ?>"
                                            data-address="<?= $other_sender['address'] ?? null; ?>"
                                            data-city="<?= $other_sender['city'] ?? null; ?>"
                                            data-zipcode="<?= $other_sender['zipcode'] ?? null; ?>"
                                            data-phone="<?= $other_sender['phone'] ?? null; ?>"
                                            data-email="<?= $other_sender['email'] ?? null; ?>"
                                            data-contact="<?= $other_sender['contact'] ?? null; ?>">
                                        <?= $other_sender['name']; ?>,
                                        <?= $other_sender['city']; ?>,
                                        <?= $other_sender['address']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else : ?>
                            <select class="other_sender" disabled></select>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Expeditor:</th>
                    <td>
                        <input type="text" name="awb[sender_name]" value="<?=$awb_details['sender_name']?>" size="40" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[sender_address]" value="<?=$awb_details['sender_address']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Localitate:</th>
                    <td><input type="text" name="awb[sender_city]" value="<?=$awb_details['sender_city']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[sender_zipcode]" value="<?=$awb_details['sender_zipcode']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Tara:</th>
                    <td><input type="text" name="awb[sender_country]" value="<?=$awb_details['sender_country']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Persoana de contact:</th>
                    <td><input type="text" name="awb[sender_contact]" value="<?=$awb_details['sender_contact']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[sender_phone]" value="<?=$awb_details['sender_phone']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[sender_email]" value="<?=$awb_details['sender_email']?>" size="40" /></td>
                </tr>
            </tbody>
        </table>

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
                    <td><input type="text" name="awb[consig_name]" value="<?=$awb_details['consig_name']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Adresa:</th>
                    <td><input type="text" name="awb[consig_address]" value="<?=$awb_details['consig_address']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Localitate:</th>
                    <td><input type="text" name="awb[consig_city]" value="<?=$awb_details['consig_city']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Cod postal:</th>
                    <td><input type="text" name="awb[consig_zipcode]" value="<?=$awb_details['consig_zipcode']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Tara:</th>
                    <td><input type="text" name="awb[consig_country]" value="<?=$awb_details['consig_country']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Persoana de contact:</th>
                    <td><input type="text" name="awb[consig_contact]" value="<?=$awb_details['consig_contact']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Telefon:</th>
                    <td><input type="text" name="awb[consig_phone]" value="<?=$awb_details['consig_phone']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Email:</th>
                    <td><input type="text" name="awb[consig_email]" value="<?=$awb_details['consig_email']?>" size="40" /></td>
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
                <tr valign="top">
                    <th scope="row">Informatii:</th>
                    <td><input type="text" name="awb[content]" value="<?=$awb_details['content']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Referinta client:</th>
                    <td><input type="text" name="awb[clientref]" value="<?=$awb_details['clientref']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Referinta cod:</th>
                    <td><input type="text" name="awb[codref]" value="<?=$awb_details['codref']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Valoare Ramburs:</th>
                    <td><input type="text" name="awb[codamount]" value="<?=$awb_details['codamount']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Numar colete:</th>
                    <td><input type="number" step="1" min="0" name="awb[pcount]" value="<?=$awb_details['pcount']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Data ridicare:</th>
                    <td><input type="date" name="awb[pickupdate]" value="<?=$awb_details['pickupdate']?>" size="40" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Servicii:</th>
                    <td>
                        <select name="awb[services]">
                            <option value="" <?= esc_attr(get_option('GLS_services')) == '' ? 'selected="selected"' : ''; ?>>Niciunul</option>
                            <option value="FDS" <?= esc_attr(get_option('GLS_services')) == 'FDS' ? 'selected="selected"' : ''; ?>>FDS</option>
                            <option value="FDS+FSS" <?= esc_attr(get_option('GLS_services')) == 'FDS+FSS' ? 'selected="selected"' : ''; ?>>FDS + FSS</option>
                            <option value="SM2" <?= esc_attr(get_option('GLS_services')) == 'SM2' ? 'selected="selected"' : ''; ?>>PreAdvice Service</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Format print:</th>
                    <td>
                        <select name="awb[printertemplate]">
                            <option value="A6" <?= esc_attr(get_option('GLS_printertemplate')) == 'A6' ? 'selected="selected"' : ''; ?>>A6 format, blank label</option>
                            <option value="A6_PP" <?= esc_attr(get_option('GLS_printertemplate')) == 'A6_PP' ? 'selected="selected"' : ''; ?>>A6 format, preprinted label</option>
                            <option value="A6_ONA4" <?= esc_attr(get_option('GLS_printertemplate')) == 'A6_ONA4' ? 'selected="selected"' : ''; ?>>A6 format, printed on A4</option>
                            <option value="A4_2x2" <?= esc_attr(get_option('GLS_printertemplate')) == 'A4_2x2' ? 'selected="selected"' : ''; ?>>A4 format, 4 labels on layout 2x2</option>
                            <option value="A4_4x1" <?= esc_attr(get_option('GLS_printertemplate')) == 'A4_4x1' ? 'selected="selected"' : ''; ?>>A4 format, 4 labels on layout 4x1</option>
                            <option value="T_85x85" <?= esc_attr(get_option('GLS_printertemplate')) == 'T_85x85' ? 'selected="selected"' : ''; ?>>85x85 mm format for thermal labels </option>
                        </select>
                    </td>
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

<script>'function'===typeof window.jQuery&&jQuery('form select').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
    jQuery($ => {
        $("input[type=submit]").on("click",function(){$(this).addClass("disabled"),$(this).val("Se generează AWB..."),setTimeout(()=>{$(this).removeClass("disabled"),$(this).val("Generează AWB")},5e3)});
        $(".other_sender").change(function() {
            $( "input[name*='sender_name']" ).val($(this).find(':selected').data('name'));
            $( "input[name*='sender_address']" ).val($(this).find(':selected').data('address'));
            $( "input[name*='sender_city']" ).val($(this).find(':selected').data('city'));
            $( "input[name*='sender_zipcode']" ).val($(this).find(':selected').data('zipcode'));
            $( "input[name*='sender_phone']" ).val($(this).find(':selected').data('phone'));
            $( "input[name*='sender_email']" ).val($(this).find(':selected').data('email'));
            $( "input[name*='sender_contact']" ).val($(this).find(':selected').data('contact'));
        });
    })
</script>
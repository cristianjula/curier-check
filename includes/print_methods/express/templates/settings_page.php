<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" id="express_settings_form" <?= (bool) esc_attr(get_option('express_valid_auth')) ? '' : 'novalidate' ?>>
        <?php
            settings_fields($settings_page);
        ?>
        <input type="hidden" name="express_valid_auth" value="<?= esc_attr(get_option('express_valid_auth')); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Cheie API Express:</th>
                    <td><input type="text"  name="express_key" value="<?= esc_attr(get_option('express_key')); ?>" size="50" /></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('express_valid_auth') ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Tip colet:</th>
                        <td>
                            <select name="express_package_type">
                                <option value="envelope" <?= esc_attr(get_option('express_package_type')) == 'envelope' ? 'selected="selected"' : ''; ?>>Plic</option>
                                <option value="package" <?= esc_attr(get_option('express_package_type')) == 'package' ? 'selected="selected"' : ''; ?>>Colet</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Serviciul implicit:</th>
                        <td>
                            <select name="express_service">
                            <?php
                                $services = CurieRO()->container->get(APIExpressClass::class)->get_services();
                                $current_service = esc_attr(get_option('express_service'));
                                if (!empty($services) && empty($services['error'])) {
                                    foreach($services as $service) {
                                        $selected = ($service['name'] == $current_service) ? 'selected="selected"' : '';
                                        echo "<option value='{$service['name']}' {$selected}>{$service['name']}</option>";
                                    }
                                } else {
                                    ?>
                                        <option value="Standard" <?= esc_attr(get_option('express_service')) == 'Standard' ? 'selected="selected"' : ''; ?>>Standard</option>
                                    <?php
                                }
                            ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur:</th>
                        <td>
                            <select name="express_retur">
                                <option value="true" <?= esc_attr(get_option('express_retur')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="false" <?= esc_attr(get_option('express_retur')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr id='tipRetur' <?= esc_attr(get_option('express_retur')) == 'false' ? 'style="display: none;"' : '' ?>>
                        <th align="left">Tip retur:</th>
                        <td>
                            <select name="express_retur_type">
                                <option value="document" <?= esc_attr(get_option('express_retur_type')) == 'document' ? 'selected="selected"' : ''; ?>>Document</option>
                                <option value="colet" <?= esc_attr(get_option('express_retur_type')) == 'colet' ? 'selected="selected"' : ''; ?>>Colet</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="express_parcel_count" value="<?= esc_attr(get_option('express_parcel_count')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Continut:</th>
                        <td><input type="text" name="express_content" value="<?= esc_attr(get_option('express_content')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Platitor:</th>
                        <td>
                            <select name="express_payer">
                                <option value="expeditor" <?= esc_attr(get_option('express_payer')) == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="destinatar" <?= esc_attr(get_option('express_payer')) == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Nume:</th>
                        <td><input type="text" name="express_name" value="<?= esc_attr(get_option('express_name')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Persoana de contact:</th>
                        <td><input type="text" name="express_contact_person" value="<?= esc_attr(get_option('express_contact_person')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Telefon:</th>
                        <td><input type="tel" name="express_phone" value="<?= esc_attr(get_option('express_phone')); ?>" size="50" required pattern="\+?\d{6,14}" /></td>
                    </tr>

                    <tr>
                        <th align="left">Email:</th>
                        <td><input type="email" name="express_email" value="<?= esc_attr(get_option('express_email')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Judet:</th>
                        <td><input type="text" name="express_county" value="<?= esc_attr(get_option('express_county')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Oras:</th>
                        <td><input type="text" name="express_city" value="<?= esc_attr(get_option('express_city')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Adresa:</th>
                        <td><input type="text" name="express_address" value="<?= esc_attr(get_option('express_address')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Cod Postal:</th>
                        <td><input type="text" name="express_postcode" value="<?= esc_attr(get_option('express_postcode')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurare:</th>
                        <td>
                            <select name="express_insurance">
                                <option value="0" <?= selected(0, get_option('express_insurance'), false) ?>>Nu</option>
                                <option value="1" <?= selected(1, get_option('express_insurance'), false) ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare Sambata:</th>
                        <td>
                            <select name="express_is_sat_delivery">
                                <option value="false" <?= esc_attr(get_option('express_is_sat_delivery')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('express_is_sat_delivery')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur document semnat:</th>
                        <td>
                            <select name="express_retur_signed_doc_delivery">
                                <option value="false" <?= esc_attr(get_option('express_retur_signed_doc_delivery')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('express_retur_signed_doc_delivery')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare 18:00 - 20:00:</th>
                        <td>
                            <select name="express_18hr_20hr_package">
                                <option value="false" <?= esc_attr(get_option('express_18hr_20hr_package')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('express_18hr_20hr_package')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Curierul sa vina cu AWB printat:</th>
                        <td>
                            <select name="express_printed_awb">
                                <option value="false" <?= esc_attr(get_option('express_printed_awb')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('express_printed_awb')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Pachete Fragile:</th>
                        <td>
                            <select name="express_is_fragile">
                                <option value="false" <?= esc_attr(get_option('express_is_fragile')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('express_is_fragile')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip printare AWB:</th>
                        <td>
                            <select name="express_page_type">
                                <option value="default" <?= esc_attr( get_option('express_page_type') ) == 'default' ? 'selected="selected"' : ''; ?>>A5</option>
                                <option value="A6" <?= esc_attr( get_option('express_page_type') ) == 'A6' ? 'selected="selected"' : ''; ?>>A6</option>

                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari notificare email</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Trimite mail la generare:</th>
                        <td>
                            <select name="express_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('express_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('express_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text"  name="express_subiect_mail" value="<?= esc_attr(get_option('express_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="express_titlu_mail" value="<?= esc_attr(get_option('express_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Continut mail:
                                <?= wc_help_tip("In text-ul email-ului urmatoarele expresii vor fi completate automat la generarea AWB-ului:<br>
                                [nr_comanda]    - Reprezinta numarul comenzii.<br>
                                [data_comanda]  - Reprezinta data in care a fost plasata comanda.<br>
                                [nr_awb]        - Reprzinta numarul AWB-ului generat.<br>
                                [tabel_produse] - Reprezinta un tabel cu capetele de coloana Nume produs / Cantitate / Pret.<br>
                                [total_comanda] - Reprezinta totalul de plata al comenzii.", true) ?>
                            </div>
                        </th>
                        <td>
                            <?php
                                $email_template = get_option('express_email_template');
                                wp_editor( $email_template, 'express_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
                            ?>
                            <p>
                                <button type="button" name="reset_email_template" class="button">Reseteaza setarile de mail la cele implicite</button>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari automatizare</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Generare AWB automata:
                                <?= wc_help_tip('Generarea AWB-ului automata in momentul in care se plaseaza o comanda noua si primeste statusul Processing.') ?>
                            </div>
                        </th>
                        <td>
                            <select name="express_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('express_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('express_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Marcheaza comanda Complete automat:
                                <?= wc_help_tip('Marcheaza comanda cu statusul Complete automat atunci cand curierul ii marcheaza statusul ca si Livrata.') ?>
                            </div>
                        </th>
                        <td>
                            <select name="express_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('express_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('express_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !(bool) get_option('express_valid_auth') ? 'hide' : '' ?>">
                <td colspan="2"><?php submit_button('Salveaza modificarile'); ?></td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:center;">
                    <p>© Copyright <script>document.write(new Date().getFullYear());</script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>'function'===typeof window.jQuery&&jQuery("form select").each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
    jQuery($ => {
        const url = "<?= curiero_get_api_url('/v1/auth/validate/express') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    api_key: $('input[name="express_key"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="express_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="express_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('select[name="express_retur"]').on('change', function(){
            if (this.value == 'true'){
                $('#tipRetur').show();
            } else {
                $('#tipRetur').hide();
            }
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'express');
            resetForm.append('action', 'curiero_reset_mail');

            const request = await fetch(ajaxurl, {
                method: 'POST',
                body: resetForm,
            });

            if (request.ok) {
                location.reload();
            } else {
                alert('Eroare, vă rugăm să ne contactați pentru a remedia problema.');
            }
        });
    })
</script>

<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$additional_services = get_option('memex_additional_services', []) ?: [];

?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" <?= (bool) esc_attr(get_option('memex_valid_auth')) ? '' : 'novalidate' ?>>
        <input type="hidden" name="memex_valid_auth" value="<?= esc_attr(get_option('memex_valid_auth')); ?>">
        <?php
        settings_fields($settings_page);
        ?>
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name">
                        <h4 style="margin:5px 0">Credentiale</h4>
                    </th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator Memex:</th>
                    <td><input type="text" name="memex_username" value="<?= esc_attr(get_option('memex_username')); ?>" size="50" placeholder="Numele utilizatorului Memex" /></td>
                </tr>

                <tr>
                    <th align="left">Parola Memex:</th>
                    <td><input type="password" name="memex_password" value="<?= esc_attr(get_option('memex_password')); ?>" size="50" placeholder="Parola utilizatorului Memex" /></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('memex_valid_auth') ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name">
                            <h4 style="margin:5px 0">Setari Expeditor</h4>
                        </th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Nume:</th>
                        <td><input type="text" name="memex_name" value="<?= esc_attr(get_option('memex_name')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Adresa:</th>
                        <td><input type="text" name="memex_address" value="<?= esc_attr(get_option('memex_address')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Oras:</th>
                        <td><input type="text" name="memex_city" value="<?= esc_attr(get_option('memex_city')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Cod postal:</th>
                        <td><input type="text" name="memex_postcode" value="<?= esc_attr(get_option('memex_postcode')); ?>" size="50" required /></td>
                    </tr>

                    <tr style="display: none;">
                        <th align="left">Codul tarii:</th>
                        <td><input type="hidden" name="memex_countrycode" value="<?= esc_attr(get_option('memex_countrycode')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Persoana de contact:</th>
                        <td><input type="text" name="memex_person" value="<?= esc_attr(get_option('memex_person')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Telefon:</th>
                        <td><input type="text" name="memex_contact" value="<?= esc_attr(get_option('memex_contact')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Email:</th>
                        <td><input type="text" name="memex_email" value="<?= esc_attr(get_option('memex_email')); ?>" size="50" required /></td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name">
                            <h4 style="margin:5px 0">Optiuni</h4>
                        </th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Serviciul implicit:</th>
                        <td>
                            <select name="memex_service_id">
                                <option value="38" <?= esc_attr(get_option('memex_service_id')) == '38' ? 'selected="selected"' : ''; ?>>38 - National Standard</option>
                                <option value="112" <?= esc_attr(get_option('memex_service_id')) == '112' ? 'selected="selected"' : ''; ?>>112 - Express 6 ore Bucuresti</option>
                                <option value="113" <?= esc_attr(get_option('memex_service_id')) == '113' ? 'selected="selected"' : ''; ?>>113 - Colete Grele</option>
                                <option value="121" <?= esc_attr(get_option('memex_service_id')) == '121' ? 'selected="selected"' : ''; ?>>121 - Loco Standard</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="memex_insurance">
                                <option value="Da" <?= esc_attr(get_option('memex_insurance')) == 'Da' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="Nu" <?= esc_attr(get_option('memex_insurance')) == 'Nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Servicii aditionale :</th>
                        <td>
                            <select name="memex_additional_services[]" multiple>
                                <option value="SSMS" <?= in_array('SSMS', $additional_services) ? 'selected' : '' ?>>Serviciu SMS</option>
                                <option value="TMP" <?= in_array('TMP', $additional_services) ? 'selected' : '' ?>>Serviciu deschidere la livrare</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Descriere continut:</th>
                        <td>
                            <select name="memex_parcel_content">
                                <option value="nu" <?= esc_attr(get_option('memex_parcel_content')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="name" <?= esc_attr(get_option('memex_parcel_content')) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                                <option value="sku" <?= esc_attr(get_option('memex_parcel_content')) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                                <option value="both" <?= esc_attr(get_option('memex_parcel_content')) == 'both' ? 'selected="selected"' : ''; ?>>Denumire+SKU produs</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Persoana Privata:</th>
                        <td>
                            <select name="memex_is_private_person">
                                <option value="false" <?= esc_attr(get_option('memex_is_private_person')) == false ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('memex_is_private_person')) == true ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="memex_package_count" value="<?= esc_attr(get_option('memex_package_count')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Numar plicuri:</th>
                        <td><input type="number" name="memex_envelope_count" value="<?= esc_attr(get_option('memex_envelope_count')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Lungime pachet:</th>
                        <td><input type="number" name="memex_parcel_length" value="<?= esc_attr(get_option('memex_parcel_length')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Inaltime pachet:</th>
                        <td><input type="number" name="memex_parcel_height" value="<?= esc_attr(get_option('memex_parcel_height')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Latime pachet:</th>
                        <td><input type="number" name="memex_parcel_width" value="<?= esc_attr(get_option('memex_parcel_width')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Greutate pachet:</th>
                        <td><input type="number" step="0.01" name="memex_parcel_weight" value="<?= esc_attr(get_option('memex_parcel_weight')); ?>" max="34" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Lungime plic:</th>
                        <td><input type="number" name="memex_envelope_length" value="<?= esc_attr(get_option('memex_envelope_length')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Inaltime plic:</th>
                        <td><input type="number" step="0.1" name="memex_envelope_height" value="<?= esc_attr(get_option('memex_envelope_height')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Latime plic:</th>
                        <td><input type="number" name="memex_envelope_width" value="<?= esc_attr(get_option('memex_envelope_width')); ?>" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Greutate plic:</th>
                        <td><input type="number" step="0.01" name="memex_envelope_weight" value="<?= esc_attr(get_option('memex_envelope_weight')); ?>" max="0.5" size="50" required></td>
                    </tr>

                    <tr>
                        <th align="left">Label format:</th>
                        <td>
                            <select name="memex_label_format">
                                <option value="PDF" <?= esc_attr(get_option('memex_label_format')) == 'PFD' ? 'selected="selected"' : ''; ?>>PDF</option>
                                <option value="PDFA4" <?= esc_attr(get_option('memex_label_format')) == 'PDFA4' ? 'selected="selected"' : ''; ?>>PDFA4 (A4 size)</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Nota colet implicita<br> (max 200 caractere):</th>
                        <td>
                            <textarea name="memex_parcel_note" lines="2" maxlength="200"><?= esc_attr(get_option('memex_parcel_note')) ?></textarea>
                            <sub style="float:right;"><span class="letterCount">0</span>/200</sub>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Solicita curier:</th>
                        <td>
                            <select name="memex_call_pickup">
                                <option value="1" <?= esc_attr(get_option('memex_call_pickup')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="0" <?= esc_attr(get_option('memex_call_pickup')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr class='memexCurier' <?= esc_attr(get_option('memex_call_pickup')) == '0' ? 'style="display: none;"' : '' ?>>
                        <th align="left">Ora minima de solicitare a curierului:</th>
                        <td>
                            <input type="time" step="1" name="memex_pickup_time" value="<?= esc_attr(get_option('memex_pickup_time')); ?>" min="09:00:00"/>
                        </td>
                    </tr>

                    <tr class='memexCurier' <?= esc_attr(get_option('memex_call_pickup')) == '0' ? 'style="display: none;"' : '' ?>>
                        <th align="left">Ora maxima de solicitare curierului:</th>
                        <td>
                            <input type="time" step="1" name="memex_max_pickup_time" value="<?= esc_attr(get_option('memex_max_pickup_time')); ?>" max="15:00:00"/>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name">
                            <h4 style="margin:5px 0">Setari notificare email</h4>
                        </th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Trimite mail la generare:</th>
                        <td>
                            <select name="memex_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('memex_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('memex_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>La generare AWB</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text" name="memex_subiect_mail" value="<?= esc_attr(get_option('memex_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="memex_titlu_mail" value="<?= esc_attr(get_option('memex_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!" /></td>
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
                            $email_template = get_option('memex_email_template');
                            wp_editor($email_template, 'memex_email_template', $settings = array('textarea_rows' => '10', 'media_buttons' => false));
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
                        <th class="wc-shipping-class-name">
                            <h4 style="margin:5px 0">Setari automatizare</h4>
                        </th>
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
                            <select name="memex_auto_generate_awb">
                                <option value="nu" <?= esc_attr(get_option('memex_auto_generate_awb')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('memex_auto_generate_awb')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="memex_auto_mark_complete">
                                <option value="nu" <?= esc_attr(get_option('memex_auto_mark_complete')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('memex_auto_mark_complete')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !(bool) get_option('memex_valid_auth') ? 'hide' : '' ?>">
                <td colspan="2"><?php submit_button('Salveaza modificarile'); ?></td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:center;">
                    <p>© Copyright <script>
                            document.write(new Date().getFullYear());
                        </script> | Un sistem prietenos de generare AWB-uri creat de <a href="https://curie.ro/" target="_blank">CurieRO</a>.</p>
                </td>
            </tr>
        </table>
    </form>
</div>

<script>
    'function' === typeof window.jQuery && jQuery("form select").each(function() {
        const e = jQuery(this).find("option").length > 4 ? {} : {
            minimumResultsForSearch: 1 / 0
        };
        jQuery(this).selectWoo(e)
    });
</script>

<script>
    jQuery($ => {
        const url = "<?= curiero_get_api_url('/v1/auth/validate/memex') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    token: {
                        UserName: $('input[name="memex_username"]').val(),
                        Password: $('input[name="memex_password"]').val()
                    },
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="memex_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="memex_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'memex');
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

        $('.letterCount').text($('textarea[name="memex_parcel_note"]').val().length);
        $('textarea[name="memex_parcel_note"]').on('keyup change', function() {
            $('.letterCount').text($(this).val().length);
        })

        $('select[name="memex_call_pickup"]').on('change', function() {
            if (this.value == '1'){
                $('.memexCurier').show();
                $('input[name$=pickup_time]').prop('required', true);
            } else {
                $('.memexCurier').hide();
                $('input[name$=pickup_time]').prop('required', false);
            }
        });
    })
</script>
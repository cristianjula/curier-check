<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
$services = CurieRO()->container->get(APIDPDClass::class)->get_services();
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post">
        <?php
            settings_fields($settings_page);
        ?>
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator DPD:</th>
                    <td><input type="text" name="dpd_username" value="<?= esc_attr(get_option('dpd_username')); ?>" size="50" placeholder="Numele utilizatorului DPD"/></td>
                </tr>

                <tr>
                    <th align="left">Parola DPD:</th>
                    <td><input type="password" name="dpd_password" value="<?= esc_attr(get_option('dpd_password')); ?>" size="50" placeholder="Parola utilizatorului DPD"/></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele DPD</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Punct expeditor:</th>
                    <td>
                        <select name="dpd_sender_id">
                        <?php
                            $senders = CurieRO()->container->get(APIDPDClass::class)->get_senders();
                            $current_sender_id = esc_attr(get_option('dpd_sender_id'));
                            if (!empty($senders)) {
                                foreach($senders as $sender) {
                                    $selected = ($sender['clientId'] == $current_sender_id) ? 'selected="selected"' : '';
                                    $senderInfo = $sender['objectName'] ? ($sender['objectName'] . ' - ' .  $sender['address']['fullAddressString']) : $sender['address']['fullAddressString'];
                                    echo "<option value='{$sender['clientId']}' {$selected}>{$senderInfo}</option>";
                                }
                            } else {
                                ?>
                                <option value="" <?= esc_attr(get_option('dpd_sender_id')) == '' ? 'selected="selected"' : ''; ?>>Utilizator implicit</option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Serviciu national implicit:</th>
                    <td>
                        <select name="dpd_service_id">
                        <?php
                            $current_national_service_id = esc_attr(get_option('dpd_service_id'));

                            if (!empty($services)) {
                                foreach($services as $service) {
                                    $selected = ($service['id'] == $current_national_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                                ?>
                                <option value="2505" <?= $current_national_service_id == '2505' ? 'selected="selected"' : ''; ?>>2505 - DPD STANDARD</option>
                                <option value="2002" <?= $current_national_service_id == '2002' ? 'selected="selected"' : ''; ?>>2002 - CLASIC NATIONAL</option>
                                <option value="2003" <?= $current_national_service_id == '2003' ? 'selected="selected"' : ''; ?>>2003 - CLASIC NATIONAL (COLET)</option>
                                <option value="2005" <?= $current_national_service_id == '2005' ? 'selected="selected"' : ''; ?>>2005 - CARGO NATIONAL</option>
                                <option value="2412" <?= $current_national_service_id == '2412' ? 'selected="selected"' : ''; ?>>2412 - PALLET ONE RO</option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left"><span style="color:red">[Premium]</span> Serviciu international implicit:</th>
                    <td>
                        <select name="dpd_international_service_id">
                        <?php
                            $current_intl_service_id = esc_attr(get_option('dpd_international_service_id'));

                            if (!empty($services)) {
                                foreach($services as $service) {
                                    $selected = ($service['id'] == $current_intl_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                                ?>
                                <option value="2212" <?= $current_intl_service_id == '2212' ? 'selected="selected"' : ''; ?>>2212 - DPD REGIONAL CEE</option>
                                <option value="2303" <?= $current_intl_service_id == '2303' ? 'selected="selected"' : ''; ?>>2303 - DPD INTERNATIONAL (RUTIER)</option>
                                <option value="2323" <?= $current_intl_service_id == '2323' ? 'selected="selected"' : ''; ?>>2323 - CERERE DE COLECTARE INTERNATIONALA (RUTIER)</option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Plata transport:</th>
                    <td>
                        <select name="dpd_courier_service_payer">
                            <option value="SENDER" <?= esc_attr(get_option('dpd_courier_service_payer')) == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= esc_attr(get_option('dpd_courier_service_payer')) == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= esc_attr(get_option('dpd_courier_service_payer')) == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/tert</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Platitor ambalaj:</th>
                    <td>
                        <select name="dpd_courier_package_payer">
                            <option value="SENDER" <?= esc_attr(get_option('dpd_courier_package_payer')) == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= esc_attr(get_option('dpd_courier_package_payer')) == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= esc_attr(get_option('dpd_courier_package_payer')) == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/tert</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Numar colete:</th>
                    <td><input type="number" name="dpd_parcel_count" value="<?= esc_attr(get_option('dpd_parcel_count')); ?>" size="50" /></td>
                </tr>

                <tr>
                    <th align="left">Valoare asigurata:</th>
                    <td>
                        <select name="dpd_declared_value">
                            <option value="0" <?= selected(0, get_option('dpd_declared_value'), false) ?>>Nu</option>
                            <option value="1" <?= selected(1, get_option('dpd_declared_value'), false) ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Tip continut colete:</th>
                    <td>
                        <select name="dpd_content_type">
                            <option value="nu" <?= esc_attr( get_option('dpd_content_type') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="name" <?= esc_attr( get_option('dpd_content_type') ) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                            <option value="sku" <?= esc_attr( get_option('dpd_content_type') ) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                            <option value="both" <?= esc_attr( get_option('dpd_content_type') ) == 'both' ? 'selected="selected"' : ''; ?>>Denumire+SKU produs</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Optiuni inainte de livrare:</th>
                    <td>
                        <select name="dpd_obpd_option">
                            <option value="" <?= esc_attr(get_option('dpd_obpd_option')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="open" <?= esc_attr(get_option('dpd_obpd_option')) == 'open' ? 'selected="selected"' : ''; ?>>Deschidere colet la livrare</option>
                            <option value="test" <?= esc_attr(get_option('dpd_obpd_option')) == 'test' ? 'selected="selected"' : ''; ?>>Testare produs la livrare</option>
                        </select>
                    </td>
                </tr>

                <tr id="dpd_obpd_return_service_id" <?= esc_attr(get_option('dpd_obpd_option')) ? '' : 'style="display: none;"' ?>>
                    <th align="left">Serviciul implicit in caz de refuz expediere:</th>
                    <td>
                        <select name="dpd_obpd_return_service_id">
                        <?php
                            $services = CurieRO()->container->get(APIDPDClass::class)->get_services();
                            $current_service_id = esc_attr(get_option('dpd_obpd_return_service_id'));
                            if (!empty($services)) {
                                foreach($services as $service) {
                                    $selected = ($service['id'] == $current_service_id) ? 'selected="selected"' : '';
                                    echo "<option value='{$service['id']}' {$selected}>{$service['id']} - {$service['name']}</option>";
                                }
                            } else {
                                ?>
                                <option value="2505" <?= esc_attr(get_option('dpd_service_id')) == '2505' ? 'selected="selected"' : ''; ?>>2505 - DPD STANDARD</option>
                                <option value="2002" <?= esc_attr(get_option('dpd_service_id')) == '2002' ? 'selected="selected"' : ''; ?>>2002 - CLASIC NATIONAL</option>
                                <option value="2003" <?= esc_attr(get_option('dpd_service_id')) == '2003' ? 'selected="selected"' : ''; ?>>2003 - CLASIC NATIONAL (COLET)</option>
                                <option value="2005" <?= esc_attr(get_option('dpd_service_id')) == '2005' ? 'selected="selected"' : ''; ?>>2005 - CARGO NATIONAL</option>
                                <option value="2412" <?= esc_attr(get_option('dpd_service_id')) == '2412' ? 'selected="selected"' : ''; ?>>2412 - PALLET ONE RO</option>
                                <?php
                            }
                        ?>
                        </select>
                    </td>
                </tr>

                <tr id="dpd_obpd_return_payer" <?= esc_attr(get_option('dpd_obpd_option')) ? '' : 'style="display: none;"' ?>>
                    <th align="left">Plata transport in caz de refuz expediere:</th>
                    <td>
                        <select name="dpd_obpd_return_payer">
                            <option value="SENDER" <?= esc_attr(get_option('dpd_obpd_return_payer')) == 'SENDER' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                            <option value="RECIPIENT" <?= esc_attr(get_option('dpd_obpd_return_payer')) == 'RECIPIENT' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            <option value="THIRD_PARTY" <?= esc_attr(get_option('dpd_obpd_return_payer')) == 'THIRD_PARTY' ? 'selected="selected"' : ''; ?>>Contract/Tert</option>
                        </select>
                    </td>
                </tr>


                <tr>
                    <th align="left">Livrare Sambata:</th>
                    <td>
                        <select name="dpd_is_sat_delivery">
                            <option value="n" <?= esc_attr(get_option('dpd_is_sat_delivery')) == 'n' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="y" <?= esc_attr(get_option('dpd_is_sat_delivery')) == 'y' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Pachete Fragile:</th>
                    <td>
                        <select name="dpd_is_fragile">
                            <option value="n" <?= esc_attr(get_option('dpd_is_fragile')) == 'n' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="y" <?= esc_attr(get_option('dpd_is_fragile')) == 'y' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Tip printare AWB:</th>
                    <td>
                        <select name="dpd_page_type">
                            <option value="A4" <?= esc_attr( get_option('dpd_page_type') ) == 'A4' ? 'selected="selected"' : ''; ?>>A4</option>
                            <option value="A6" <?= esc_attr( get_option('dpd_page_type') ) == 'A6' ? 'selected="selected"' : ''; ?>>A6</option>
                            <option value="A4_4xA6" <?= esc_attr( get_option('dpd_page_type') ) == 'A4_4xA6' ? 'selected="selected"' : ''; ?>>A4_4xA6</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Prioritate nota colet afisata pe awb:</th>
                    <td>
                        <select name="dpd_parcel_note_priority">
                            <option value="admin" <?= esc_attr( get_option('dpd_parcel_note_priority') ) == 'admin' ? 'selected="selected"' : ''; ?>>Nota colet admin</option>
                            <option value="client" <?= esc_attr( get_option('dpd_parcel_note_priority') ) == 'client' ? 'selected="selected"' : ''; ?>>Nota colet client</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Nota colet implicita<br> (max 200 caractere):</th>
                    <td>
                        <textarea name="dpd_parcel_note" lines="2" maxlength="200"><?= esc_attr( get_option('dpd_parcel_note') ) ?></textarea>
                        <sub style="float:right;"><span class="letterCount">0</span>/200</sub>
                    </td>
                </tr>

                <tr>
                    <th>
                        <div class="has_help_tip">
                            Greutate colet standard (in kg):
                            <?= wc_help_tip('In cazul in care greutatea standard nu este completata, ea va fi calculata automat in functie de parametrii configurati la nivel de produs.') ?>
                        </div>
                    </th>
                    <td>
                        <input type="text" name="dpd_force_weight" value="<?= esc_attr( get_option('dpd_force_weight') ); ?>" size="50" />
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
                        <select name="dpd_trimite_mail">
                            <option value="nu" <?= esc_attr(get_option('dpd_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr(get_option('dpd_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Subiect mail:</th>
                    <td><input type="text"  name="dpd_subiect_mail" value="<?= esc_attr(get_option('dpd_subiect_mail')); ?>" size="50" /></td>
                </tr>

                <tr>
                    <th align="left">Titlu antet mail:</th>
                    <td><input type="text" name="dpd_titlu_mail" value="<?= esc_attr(get_option('dpd_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                            $email_template = get_option('dpd_email_template');
                            wp_editor( $email_template, 'dpd_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                        <select name="dpd_auto_generate_awb">
                            <option value="nu" <?= esc_attr( get_option('dpd_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('dpd_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                        <select name="dpd_auto_mark_complete">
                            <option value="nu" <?= esc_attr( get_option('dpd_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('dpd_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="endtable">
            <tr>
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
    jQuery( $ => {
        const url = "<?= curiero_get_api_url('/v1/auth/validate/dpd') ?>";


        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: $('input[name="dpd_username"]').val(),
                    password: $('input[name="dpd_password"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'dpd');
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

        $('.letterCount').text($('textarea[name="dpd_parcel_note"]').val().length);
        $('textarea[name="dpd_parcel_note"]').on('keyup change', function(){$('.letterCount').text($(this).val().length);})

        $('select[name="dpd_obpd_option"]').on('change', function(){
            if(this.value !== ''){
                $('#dpd_obpd_return_service_id').show();
                $('#dpd_obpd_return_payer').show();
            } else {
                $('#dpd_obpd_return_service_id').hide();
                $('#dpd_obpd_return_payer').hide();
            }
        });

    })
</script>

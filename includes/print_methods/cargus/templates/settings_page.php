<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$obj_urgent = CurieRO()->container->get(UrgentCargusAPI::class);
$resultLocations = $obj_urgent->callMethod('PickupLocations/GetForClient', [], 'GET');
$resultMessage = $resultLocations['message'];
$locations = json_decode($resultMessage, true);
$valid_auth = ($resultLocations['status'] === 200 && $resultMessage !== "Failed to authenticate!");
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<style>
    .cargus_auth{border:solid 1px;padding:20px}
</style>

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
                    <th align="left">Utilizator Cargus:</th>
                    <td><input type="text"  name="uc_username" value="<?= esc_attr(get_option('uc_username')); ?>" size="50" placeholder="Numele utilizatorului Cargus"/></td>
                </tr>

                <tr>
                    <th align="left">Parola Cargus:</th>
                    <td><input type="password"  name="uc_password" value="<?= esc_attr(get_option('uc_password')); ?>" size="50" placeholder="Parola utilizatorului Cargus"/></td>
                </tr>

                <tr>
                    <th align="left">Cheie API Cargus:</th>
                    <td><input type="text"  name="uc_apikey" value="<?= esc_attr(get_option('uc_apikey')); ?>" size="50" placeholder="Cheie API Cargus"/></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>

            </tbody>
        </table>

        <div class="<?= !$valid_auth ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">ID punct ridicare:</th>
                        <td>
                            <select name="uc_punct_ridicare"> <?php
                                if($valid_auth)
                                    foreach ($locations as $location) {
                                        ?><option value="<?= $location['LocationId']; ?>" <?= esc_attr(get_option('uc_punct_ridicare')) == $location['LocationId'] ? 'selected="selected"' : ''; ?>><?= $location['Name']; ?></option><?php
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">ID tarif:</th>
                        <?php
                            $resultPriceTables = $obj_urgent->callMethod('PriceTables', [], 'GET');
                            $resultMessage = $resultPriceTables['message'];
                            $arrayPriceTables = json_decode($resultMessage, true);
                            if ($valid_auth) {
                                ?> <td><select name="uc_price_table_id"> <?php
                                foreach ($arrayPriceTables as $price_table) {
                                    ?><option value="<?= $price_table['PriceTableId']; ?>" <?= esc_attr(get_option('uc_price_table_id')) == $price_table['PriceTableId'] ? 'selected="selected"' : ''; ?>><?= $price_table['Name']; ?></option><?php
                                }
                                ?> </select></td> <?php
                            } else {
                                ?> <td><input type="text" name="uc_price_table_id" value="<?= esc_attr(get_option('uc_price_table_id')); ?>" size="50" /></td>
                        <?php } ?>
                    </tr>

                    <tr>
                        <th align="left">Tip serviciu:</th>
                        <td>
                            <?php $uc_tip_serviciu = esc_attr(get_option('uc_tip_serviciu')); ?>
                            <select name="uc_tip_serviciu">
                                <option value="34" <?= $uc_tip_serviciu == '34' ? 'selected="selected"' : ''; ?>>Economic Standard</option>
                                <option value="35" <?= $uc_tip_serviciu == '35' ? 'selected="selected"' : ''; ?>>Standard Plus</option>
                                <option value="36" <?= $uc_tip_serviciu == '36' ? 'selected="selected"' : ''; ?>>Palet Standard</option>
                                <option value="39" <?= $uc_tip_serviciu == '39' ? 'selected="selected"' : ''; ?>>Multipiece / Economic Standard M</option>
                                <option value="40" <?= $uc_tip_serviciu == '40' ? 'selected="selected"' : ''; ?>>Economic Standard M Plus</option>
                                <option value="1" <?= $uc_tip_serviciu == '1' ? 'selected="selected"' : ''; ?>>Standard</option>
                                <option value="4" <?= $uc_tip_serviciu == '4' ? 'selected="selected"' : ''; ?>>Business Partener</option>
                                <option value="38" <?= $uc_tip_serviciu == '38' ? 'selected="selected"' : ''; ?>>Ship & Go / PUDO Delivery</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Plata transport:</th>
                        <td>
                            <select name="uc_plata_transport">
                                <option value="1" <?= esc_attr(get_option('uc_plata_transport')) == '1' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="2" <?= esc_attr(get_option('uc_plata_transport')) == '2' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Plata ramburs:</th>
                        <td>
                            <select name="uc_plata_ramburs">
                                <option value="1" <?= esc_attr(get_option('uc_plata_ramburs')) == '1' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="2" <?= esc_attr(get_option('uc_plata_ramburs')) == '2' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="uc_nr_colete" value="<?= esc_attr(get_option('uc_nr_colete')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Numar plicuri:</th>
                        <td><input type="number" name="uc_nr_plicuri" value="<?= esc_attr(get_option('uc_nr_plicuri')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="uc_asigurare">
                                <option value="0" <?= esc_attr(get_option('uc_asigurare')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?= esc_attr(get_option('uc_asigurare')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Deschidere la livrare:</th>
                        <td>
                            <select name="uc_deschidere">
                                <option value="0" <?= esc_attr(get_option('uc_deschidere')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?= esc_attr(get_option('uc_deschidere')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare matinala:</th>
                        <td>
                            <select name="uc_matinal">
                                <option value="0" <?= esc_attr(get_option('uc_matinal')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?= esc_attr(get_option('uc_matinal')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare Sambata:</th>
                        <td>
                            <select name="uc_sambata">
                                <option value="0" <?= esc_attr(get_option('uc_sambata')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?= esc_attr(get_option('uc_sambata')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip continut colete:</th>
                        <td>
                            <select name="uc_descrie_continut">
                                <option value="nu" <?= esc_attr( get_option('uc_descrie_continut') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="1" <?= esc_attr( get_option('uc_descrie_continut') ) == '1' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                                <option value="sku" <?= esc_attr( get_option('uc_descrie_continut') ) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                                <option value="both" <?= esc_attr( get_option('uc_descrie_continut') ) == 'both' ? 'selected="selected"' : ''; ?>>Denumire+SKU produs</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Format tiparire AWB:</th>
                        <td>
                            <select name="uc_print_format">
                                <option value="0" <?= esc_attr(get_option('uc_print_format')) == '0' ? 'selected="selected"' : ''; ?>>A4</option>
                                <option value="1" <?= esc_attr(get_option('uc_print_format')) == '1' ? 'selected="selected"' : ''; ?>>10x14</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tiparire dubla AWB:</th>
                        <td>
                            <select name="uc_print_once">
                                <option value="0" <?= esc_attr(get_option('uc_print_once')) == '0' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="1" <?= esc_attr(get_option('uc_print_once')) == '1' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Observatii:</th>
                        <td><input type="text" placeholder="A se contacta telefonic" name="uc_observatii" value="<?= esc_attr(get_option('uc_observatii')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Referinta Serie Client:</th>
                        <td><input type="text" name="uc_serie_client" value="<?= esc_attr(get_option('uc_serie_client')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Lungime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="uc_force_length" value="<?= esc_attr( get_option('uc_force_length') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Latime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="uc_force_width" value="<?= esc_attr( get_option('uc_force_width') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Inaltime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="uc_force_height" value="<?= esc_attr( get_option('uc_force_height') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Greutate colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="uc_force_weight" value="<?= esc_attr( get_option('uc_force_weight') ); ?>" size="50" /></td>
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
                            <select name="uc_trimite_mail">
                                <option value="1" <?= esc_attr(get_option('uc_trimite_mail')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="0" <?= esc_attr(get_option('uc_trimite_mail')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text"  name="uc_subiect_mail" value="<?= esc_attr(get_option('uc_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="uc_titlu_mail" value="<?= esc_attr(get_option('uc_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                $email_template = get_option('uc_email_template');
                                wp_editor( $email_template, 'uc_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                            <select name="uc_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('uc_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('uc_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="uc_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('uc_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('uc_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !$valid_auth ? 'hide' : '' ?>">
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/cargus') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    urgent_user: $('input[name="uc_username"]').val(),
                    urgent_pass: $('input[name="uc_password"]').val(),
                    urgent_apiKey: $('input[name="uc_apikey"]').val()
                }),
                dataType: "json",
                success: function(response) {
                    if(response['success']){
                        responseDiv.text('Autentificare reusita.').css('color', '#34a934');
                        submitBtn.click();
                    } else {
                        responseDiv.text('Autentificare esuata.').css('color', '#f44336');
                        submitBtn.click();
                    }
                }
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
            resetForm.append('courier', 'cargus');
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

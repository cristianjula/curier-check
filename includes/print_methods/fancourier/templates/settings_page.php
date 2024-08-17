<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$valid_auth = (bool) get_option('fan_valid_auth', false);
$client_ids = [];
$services = [];

if ($valid_auth) {
    $api_class = CurieRO()->container->get(APIFanCourierClass::class);
    $client_ids = $api_class->getClientIds();
    $services = $api_class->getServices();
}
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" <?= $valid_auth ? '' : 'novalidate' ?> >
        <?php
            settings_fields($settings_page);
        ?>
        <input type="hidden" name="fan_valid_auth" value="<?= $valid_auth; ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator SelfAWB:</th>
                    <td><input type="text" name="fan_user" value="<?= esc_attr( get_option('fan_user') ); ?>" size="50" placeholder="Numele utilizatorului FanCourier SelfAWB"/></td>
                </tr>

                <tr>
                    <th align="left">Parola SelfAWB:</th>
                    <td><input type="password" name="fan_password" value="<?= esc_attr( get_option('fan_password') ); ?>" size="50" placeholder="Parola utilizatorului FanCourier SelfAWB"/></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= ! $valid_auth ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Punct de ridicare:</th>
                        <td>
                            <select name="fan_clientID" required>
                                <option value=""></option>
                                <?php foreach($client_ids as $client_id) { ?>
                                    <option <?= selected($client_id['id'], get_option('fan_clientID')) ?> value="<?= $client_id['id'] ?>">
                                        <?= $client_id['name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip serviciu:</th>
                        <td>
                            <select name="fan_service" required>
                                <option value=""></option>
                                <?php foreach($services as $service) { ?>
                                    <option <?= selected($service, get_option('fan_service')) ?> value="<?= $service ?>">
                                        <?= $service ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="fan_nr_colete" value="<?= esc_attr( get_option('fan_nr_colete') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Numar plicuri:</th>
                        <td><input type="number" name="fan_nr_plicuri" value="<?= esc_attr( get_option('fan_nr_plicuri') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Plata transport: </th>
                        <td>
                            <select name="fan_plata_transport">
                                <option value="expeditor" <?= esc_attr( get_option('fan_plata_transport') ) == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="destinatar" <?= esc_attr( get_option('fan_plata_transport') ) == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Plata ramburs:</th>
                        <td>
                            <select name="fan_plata_ramburs">
                                <option value="expeditor" <?= esc_attr( get_option('fan_plata_ramburs') ) == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="destinatar" <?= esc_attr( get_option('fan_plata_ramburs') ) == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="fan_asigurare">
                                <option value="nu" <?= esc_attr( get_option('fan_asigurare') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('fan_asigurare') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Deschidere la livrare:</th>
                        <td>
                            <select name="fan_deschidere">
                                <option value="" <?= esc_attr( get_option('fan_deschidere') ) == '' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('fan_deschidere') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare sambata:</th>
                        <td>
                            <select name="fan_sambata">
                                <option value="" <?= esc_attr( get_option('fan_sambata') ) == '' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('fan_sambata') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Optiune ePOD / oPOD:</th>
                        <td>
                            <select name="fan_epod_opod">
                                <option value="nu" <?= esc_attr( get_option('fan_epod_opod') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="epod" <?= esc_attr( get_option('fan_epod_opod') ) == 'epod' ? 'selected="selected"' : ''; ?>>ePOD</option>
                                <option value="opod" <?= esc_attr( get_option('fan_epod_opod') ) == 'opod' ? 'selected="selected"' : ''; ?>>oPOD</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip printare AWB:</th>
                        <td>
                            <select name="fan_page_type">
                                <option value="" <?= esc_attr( get_option('fan_page_type') ) == '' ? 'selected="selected"' : ''; ?>>Conform setarilor din SelfAWB</option>
                                <option value="A4" <?= esc_attr( get_option('fan_page_type') ) == 'A4' ? 'selected="selected"' : ''; ?>>A4</option>
                                <option value="A5" <?= esc_attr( get_option('fan_page_type') ) == 'A5' ? 'selected="selected"' : ''; ?>>A5</option>
                                <option value="A6" <?= esc_attr( get_option('fan_page_type') ) == 'A6' ? 'selected="selected"' : ''; ?>>A6 (valabil doar pentru ePOD)</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Date personale:</th>
                        <td><input type="text" name="fan_personal_data" value="<?= esc_attr( get_option('fan_personal_data') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Contact expeditor:</th>
                        <td><input type="text" name="fan_contact_exp" value="<?= esc_attr( get_option('fan_contact_exp') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Observatii:</th>
                        <td><input type="text" name="fan_observatii" value="<?= esc_attr( get_option('fan_observatii') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Descriere continut:</th>
                        <td>
                            <select name="fan_descriere_continut">
                                <option value="nu" <?= esc_attr( get_option('fan_descriere_continut') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="name" <?= esc_attr( get_option('fan_descriere_continut') ) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                                <option value="sku" <?= esc_attr( get_option('fan_descriere_continut') ) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                                <option value="both" <?= esc_attr( get_option('fan_descriere_continut') ) == 'both' ? 'selected="selected"' : ''; ?>>Denumire+SKU produs</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Lungime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="fan_force_length" value="<?= esc_attr( get_option('fan_force_length') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Latime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="fan_force_width" value="<?= esc_attr( get_option('fan_force_width') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Inaltime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="fan_force_height" value="<?= esc_attr( get_option('fan_force_height') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Greutate colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="fan_force_weight" value="<?= esc_attr( get_option('fan_force_weight') ); ?>" size="50" /></td>
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
                            <select name="fan_trimite_mail">
                                <option value="da" <?= esc_attr( get_option('fan_trimite_mail') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="nu" <?= esc_attr( get_option('fan_trimite_mail') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text" name="fan_subiect_mail" value="<?= esc_attr( get_option('fan_subiect_mail') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="fan_titlu_mail" value="<?= esc_attr(get_option('fan_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                $email_template = get_option('fan_email_template');
                                wp_editor( $email_template, 'fan_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                            <select name="fan_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('fan_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('fan_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="fan_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('fan_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('fan_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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

<script>'function'===typeof window.jQuery&&jQuery('form select:not([name="fan_clientID"])').each(function(){const e=jQuery(this).find("option").length>4?{}:{minimumResultsForSearch:1/0};jQuery(this).selectWoo(e)});</script>

<script>
    jQuery('form select[name="fan_clientID"]').select2({ placeholder: "Alege un Punct de Ridicare"});
    jQuery('form select[name="fan_service"]').select2({ placeholder: "Alege un tip de serviciu"});

    jQuery(($) => {

        const url = "<?= curiero_get_api_url('/v2/auth/validate/fan') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    fan_user: $('input[name="fan_user"]').val(),
                    fan_pass: $('input[name="fan_password"]').val(),
                    fan_id: $('input[name="fan_clientID"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="fan_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="fan_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'fancourier');
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
    });
</script>

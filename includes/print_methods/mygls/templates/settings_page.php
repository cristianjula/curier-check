<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="POST">
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
                    <th align="left">Utilizator MyGLS:</th>
                    <td><input type="text" name="MyGLS_user" value="<?= esc_attr(get_option('MyGLS_user')); ?>" size="50" placeholder="Numele utilizatorului MyGLS" required/></td>
                </tr>

                <tr>
                    <th align="left">Parola MyGLS:</th>
                    <td><input type="password" name="MyGLS_password" value="<?= esc_attr(get_option('MyGLS_password')); ?>" size="50" placeholder="Parola utilizatorului MyGLS" required/></td>
                </tr>

                <tr>
                    <th align="left">ID Client MyGLS:</th>
                    <td><input type="text" name="MyGLS_clientnumber" value="<?= esc_attr(get_option('MyGLS_clientnumber')); ?>" size="50" placeholder="Numar client MyGLS" required/></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari expeditor</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Nume expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_name" value="<?= esc_attr(get_option('MyGLS_sender_name')); ?>" size="50" placeholder="Ex: SC. FirmaTest SRL."/></td>
                </tr>

                <tr>
                    <th align="left">Adresa expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_address" value="<?= esc_attr(get_option('MyGLS_sender_address')); ?>" size="50" placeholder="Ex: Str. Aurel Vlaicu Nr. 3-A"/></td>
                </tr>

                <tr>
                    <th align="left">Localitate expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_city" value="<?= esc_attr(get_option('MyGLS_sender_city')); ?>" size="50" placeholder="Ex: Timisoara"/></td>
                </tr>

                <tr>
                    <th align="left">Cod postal expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_zipcode" value="<?= esc_attr(get_option('MyGLS_sender_zipcode')); ?>" size="50" placeholder="Ex: 300702"/></td>
                </tr>

                <tr>
                    <th align="left">Telefon expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_phone" value="<?= esc_attr(get_option('MyGLS_sender_phone')); ?>" size="50" placeholder="Ex: 0799123456"/></td>
                </tr>

                <tr>
                    <th align="left">Email expeditor:</th>
                    <td><input type="text" name="MyGLS_sender_email" value="<?= esc_attr(get_option('MyGLS_sender_email')); ?>" size="50" placeholder="Ex: test@email.com"/></td>
                </tr>

                <tr>
                    <th align="left">Nume persoana contacta:</th>
                    <td><input type="text" name="MyGLS_sender_contact" value="<?= esc_attr(get_option('MyGLS_sender_contact')); ?>" size="50" placeholder="Ex: Ionut Popescu"/></td>
                </tr>

                <tr>
                    <th align="left" style="padding: 10px 0">
                        [Optional] Alti expeditori
                    </th>
                    <td>
                        <button type="button" name="add_other_sender" class="button">Adauga alt expeditor</button>
                    </td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Nume expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][name]" value="" size="50" placeholder="Ex: SC. FirmaTest SRL."/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Adresa expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][address]" value="" size="50" placeholder="Ex: Str. Aurel Vlaicu Nr. 3-A"/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Localitate expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][city]" value="" size="50" placeholder="Ex: Timisoara"/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Cod postal expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][zipcode]" value="" size="50" placeholder="Ex: 300702"/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Telefon expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][phone]" value="" size="50" placeholder="Ex: 0799123456"/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Email expeditor:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][email]" value="" size="50" placeholder="Ex: test@email.com"/></td>
                </tr>

                <tr class="other_sender_row">
                    <th align="left">Nume persoana contact:</th>
                    <td><input disabled type="text" name="MyGLS_other_senders[new][contact]" value="" size="50" placeholder="Ex: Ionut Popescu"/></td>
                </tr>

                <?php
                    $other_senders = maybe_unserialize(get_option('MyGLS_other_senders')) ?: [];
                    $sender_counter = 1;
                    foreach ($other_senders as $key => $sender):
                ?>
                <tr>
                    <th align="left">
                        <div style="display:grid;grid-gap:.5rem;align-items:center;">
                            <div>
                                Expeditor <?= $sender_counter++; ?>:
                            </div>
                            <button type="button" class="remove_other_sender button button-secondary" name="remove_other_sender" value="<?= $key; ?>">Sterge</button>
                        </div>
                    </th>
                    <td>
                        <ul>
                            <li><b>Nume: </b><?= $sender['name']; ?></li>
                            <li><b>Adresa: </b><?= $sender['address']; ?></li>
                            <li><b>Localitate: </b><?= $sender['city']; ?></li>
                            <li><b>Cod postal: </b><?= $sender['zipcode']; ?></li>
                            <li><b>Telefon: </b><?= $sender['phone']; ?></li>
                            <li><b>Email: </b><?= $sender['email']; ?></li>
                            <li><b>Persoana contact: </b><?= $sender['contact']; ?></li>
                        </ul>
                    </td>
                </tr>
                <?php
                    endforeach;
                ?>
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
                    <th align="left">
                        <div class="has_help_tip">
                            Servicii:
                            <?= wc_help_tip("* Pentru <b>FDS</b> asigurati-va ca adresa de email a clientului este completata in comanda si este valida. <br>
                        * Pentru <b>FDS + FSS</b> trebuie sa va asigurati ca criteriul mentionat mai sus este indeplinit, si numarul de telefon al clientului respecta standardul international.<br>
                        * Pentru <b>PreAdvice Service</b> trebuie sa va asigurati ca numarul de telefon al clientului respecta standardul international.", true) ?>
                        </div>
                    </th>
                    <td>
                        <select name="MyGLS_services">
                            <option value="" <?= esc_attr(get_option('MyGLS_services')) == '' ? 'selected="selected"' : ''; ?>>Niciunul</option>
                            <option value="FDS" <?= esc_attr(get_option('MyGLS_services')) == 'FDS' ? 'selected="selected"' : ''; ?>>FDS</option>
                            <option value="FDS+FSS" <?= esc_attr(get_option('MyGLS_services')) == 'FDS+FSS' ? 'selected="selected"' : ''; ?>>FDS + FSS</option>
                            <option value="SM2" <?= esc_attr(get_option('MyGLS_services')) == 'SM2' ? 'selected="selected"' : ''; ?>>PreAdvice Service</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Format print:</th>
                    <td>
                        <select name="MyGLS_printertemplate">
                            <option value="A4_2x2" <?= esc_attr(get_option('MyGLS_printertemplate')) == 'A4_2x2' ? 'selected="selected"' : ''; ?>>A4 format, 4 labels on layout 2x2</option>
                            <option value="A4_4x1" <?= esc_attr(get_option('MyGLS_printertemplate')) == 'A4_4x1' ? 'selected="selected"' : ''; ?>>A4 format, 4 labels on layout 4x1</option>
                            <option value="Thermo" <?= esc_attr(get_option('MyGLS_printertemplate')) == 'Thermo' ? 'selected="selected"' : ''; ?>>85x85 mm format for thermal labels </option>
                            <option value="Connect" <?= esc_attr(get_option('MyGLS_printertemplate')) == 'Connect' ? 'selected="selected"' : ''; ?>>Connect 21x28 cm format</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Observatii:</th>
                    <td><input type="text" name="GLS_observatii" value="<?= esc_attr(get_option('MyGLS_observatii')); ?>" size="50" placeholder="Ex: A se contacta telefonic"/></td>
                </tr>

                <tr>
                    <th align="left">Arata nota client dupa observatii:</th>
                    <td>
                        <select name="MyGLS_show_client_note">
                            <option value="0" <?= esc_attr(get_option('MyGLS_show_client_note')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="1" <?= esc_attr(get_option('MyGLS_show_client_note')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Arata ID comanda dupa observatii:</th>
                    <td>
                        <select name="MyGLS_show_order_id">
                            <option value="0" <?= esc_attr(get_option('MyGLS_show_order_id')) == '0' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="1" <?= esc_attr(get_option('MyGLS_show_order_id')) == '1' ? 'selected="selected"' : ''; ?>>Da</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Descrie continut dupa observatii:</th>
                    <td>
                        <select name="MyGLS_show_content">
                            <option value="nu" <?= esc_attr( get_option('MyGLS_show_content') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="name" <?= esc_attr( get_option('MyGLS_show_content') ) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                            <option value="sku" <?= esc_attr( get_option('MyGLS_show_content') ) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Numar colete:</th>
                    <td>
                        <input type="number" min="0" step="1" name="MyGLS_pcount" value="<?= esc_attr(get_option('MyGLS_pcount')); ?>" placeholder="Numar colete"/>
                    </td>
                </tr>

                <tr>
                    <th align="left">
                        <div class="has_help_tip">
                            Curatare automata AWB:
                            <?= wc_help_tip('Aceasta functie curata baza de date de AWB-urile vechi stocate la dumneavoastra in site. AWB-urile raman disponibile in continuare in platforma MyGLS.') ?>
                        </div>
                    </th>
                    <td>
                        <select name="MyGLS_auto_cleanup_awb">
                            <option value="nu" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Dezactivata</option>
                            <option value="30" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == '30' ? 'selected="selected"' : ''; ?>>30 zile</option>
                            <option value="60" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == '60' ? 'selected="selected"' : ''; ?>>60 zile</option>
                            <option value="90" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == '90' ? 'selected="selected"' : ''; ?>>90 zile</option>
                            <option value="180" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == '180' ? 'selected="selected"' : ''; ?>>180 zile</option>
                            <option value="365" <?= esc_attr( get_option('MyGLS_auto_cleanup_awb') ) == '365' ? 'selected="selected"' : ''; ?>>365 zile</option>
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
                        <select name="MyGLS_trimite_mail">
                            <option value="da" <?= esc_attr(get_option('MyGLS_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            <option value="nu" <?= esc_attr(get_option('MyGLS_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Subiect mail:</th>
                    <td><input type="text" name="GLS_subiect_mail" value="<?= esc_attr(get_option('MyGLS_subiect_mail')); ?>" size="50" placeholder="Ex: AWB GLS a fost generat pentru comanda dumneavoastra"/></td>
                </tr>

                <tr>
                    <th align="left">Titlu antet mail:</th>
                    <td><input type="text" name="GLS_titlu_mail" value="<?= esc_attr(get_option('MyGLS_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                            $email_template = get_option('MyGLS_email_template');
                            wp_editor( $email_template, 'MyGLS_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                        <select name="MyGLS_auto_generate_awb">
                            <option value="nu" <?= esc_attr( get_option('MyGLS_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('MyGLS_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                        <select name="MyGLS_auto_mark_complete">
                            <option value="nu" <?= esc_attr( get_option('MyGLS_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('MyGLS_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
    jQuery($ => {
        const url = "<?= curiero_get_api_url('/v2/auth/validate/mygls') ?>";

        $('button[name="add_other_sender"]').on('click', function(){
            $('.other_sender_row').toggle();
            $('.other_sender_row input').prop('disabled', (i, v) => !v );
        });

        $('button.remove_other_sender').on('click', async function () {
            const remove_GLS_other_sender = this.value;

            const removeForm = new FormData();
            removeForm.append('remove_GLS_other_sender', remove_GLS_other_sender);
            removeForm.append('action', 'curiero_remove_other_mygls_sender');

            const request = await fetch(ajaxurl, {
                method: 'POST',
                body: removeForm,
            });

            if (request.ok) {
                location.reload();
            } else {
                alert('Eroare la stergere, vă rugăm să ne contactați pentru a remedia problema.');
            }
        });

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: $('input[name="MyGLS_user"]').val(),
                    password: $('input[name="MyGLS_password"]').val(),
                    clientNumber: $('input[name="MyGLS_clientnumber"]').val(),
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
            resetForm.append('courier', 'mygls');
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

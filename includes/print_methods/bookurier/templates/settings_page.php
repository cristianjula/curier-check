<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
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
                    <th align="left">Utilizator Bookurier:</th>
                    <td><input type="text" name="bookurier_user" value="<?= esc_attr(get_option('bookurier_user')); ?>" size="50" placeholder="Numele utilizatorului Bookurier"/></td>
                </tr>

                <tr>
                    <th align="left">Parola Bookurier:</th>
                    <td><input type="password" name="bookurier_password" value="<?= esc_attr(get_option('bookurier_password')); ?>" size="50" placeholder="Parola utilizatorului Bookurier"/></td>
                </tr>

                <tr>
                    <th align="left">Cod Client Bookurier:</th>
                    <td><input type="text" name="bookurier_senderid" value="<?= esc_attr(get_option('bookurier_senderid')); ?>" size="50" placeholder="Cod Client Bookurier"/></td>
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
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Serviciul de livrare:</th>
                    <?php $service = get_option('bookurier_services'); ?>

                    <td>
                        <select name="bookurier_services">
                            <option value="1" <?= selected(1, $service, false) ?>>Bucuresti 24h</option>
                            <option value="3" <?= selected(3, $service, false) ?>>Metropolitan</option>
                            <option value="5" <?= selected(5, $service, false) ?>>Extins</option>
                            <option value="7" <?= selected(7, $service, false) ?>>Bucuresti Today</option>
                            <option value="8" <?= selected(8, $service, false) ?>>National Economic</option>
                            <option value="9" <?= selected(9, $service, false) ?>>National Standard</option>
                            <option value="11" <?= selected(11, $service, false) ?>>National Premium</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Observatii:</th>
                    <td><input type="text" name="bookurier_observatii" value="<?= esc_attr(get_option('bookurier_observatii')); ?>" size="50" placeholder="Ex: A se contacta telefonic"/></td>
                </tr>

                <tr>
                    <th align="left">Numar colete:</th>
                    <td>
                        <input type="number" min="0" step="1" name="bookurier_pcount" value="<?= esc_attr(get_option('bookurier_pcount')); ?>" placeholder="Numar colete"/>
                    </td>
                </tr>

                <tr>
                    <th align="left">Valoare asigurata:</th>
                    <td>
                        <select name="bookurier_insurance_val">
                            <option value="0" <?= selected(0, get_option('bookurier_insurance_val'), false) ?>>Nu</option>
                            <option value="1" <?= selected(1, get_option('bookurier_insurance_val'), false) ?>>Da</option>
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
                        <select name="bookurier_trimite_mail">
                            <option value="da" <?= esc_attr(get_option('bookurier_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            <option value="nu" <?= esc_attr(get_option('bookurier_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th align="left">Subiect mail:</th>
                    <td><input type="text" name="bookurier_subiect_mail" value="<?= esc_attr(get_option('bookurier_subiect_mail')); ?>" size="50" placeholder="Ex: AWB bookurier a fost generat pentru comanda dumneavoastra"/></td>
                </tr>

                <tr>
                    <th align="left">Titlu antet mail:</th>
                    <td><input type="text" name="bookurier_titlu_mail" value="<?= esc_attr(get_option('bookurier_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                            $email_template = get_option('bookurier_email_template');
                            wp_editor( $email_template, 'bookurier_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                        <select name="bookurier_auto_generate_awb">
                            <option value="nu" <?= esc_attr( get_option('bookurier_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('bookurier_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                        <select name="bookurier_auto_mark_complete">
                            <option value="nu" <?= esc_attr( get_option('bookurier_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                            <option value="da" <?= esc_attr( get_option('bookurier_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/bookurier') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    userid: $('input[name="bookurier_user"]').val(),
                    pwd: $('input[name="bookurier_password"]').val(),
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
            resetForm.append('courier', 'bookurier');
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

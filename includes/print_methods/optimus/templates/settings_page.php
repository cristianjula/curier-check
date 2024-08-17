<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" <?= (bool) esc_attr(get_option('optimus_valid_auth')) ? '' : 'novalidate' ?>>
    <?php
        settings_fields($settings_page);
    ?>
    <input type="hidden" name="optimus_valid_auth" value="<?= esc_attr(get_option('optimus_valid_auth')); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator Optimus:</th>
                    <td><input type="text"  name="optimus_username" value="<?= esc_attr(get_option('optimus_username')); ?>" size="50" placeholder="Numele utilizatorului Optimus" /></td>
                </tr>

                <tr>
                    <th align="left">Cheie API Optimus:</th>
                    <td><input type="text"  name="optimus_key" value="<?= esc_attr(get_option('optimus_key')); ?>" size="50" placeholder="Cheia utilizatorului Optimus" /></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('optimus_valid_auth') ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Optiuni</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="optimus_count" value="<?= esc_attr(get_option('optimus_count')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Continut:</th>
                        <td><input type="text" name="optimus_parcel_content" value="<?= esc_attr(get_option('optimus_parcel_content')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Greutate pachet:</th>
                        <td><input type="number" step="0.01" name="optimus_parcel_weight" value="<?= esc_attr(get_option('optimus_parcel_weight')); ?>" size="50" required ></td>
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
                            <select name="optimus_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('optimus_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('optimus_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text"  name="optimus_subiect_mail" value="<?= esc_attr(get_option('optimus_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="optimus_titlu_mail" value="<?= esc_attr(get_option('optimus_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                $email_template = get_option('optimus_email_template');
                                wp_editor( $email_template, 'optimus_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                            <select name="optimus_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('optimus_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('optimus_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="optimus_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('optimus_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('optimus_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr  class="<?= !(bool) get_option('optimus_valid_auth') ? 'hide' : '' ?>">
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/optimus') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: $('input[name="optimus_username"]').val(),
                    api_key: $('input[name="optimus_key"]').val()
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="optimus_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="optimus_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'optimus');
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

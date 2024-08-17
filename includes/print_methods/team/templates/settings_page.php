<?php
// Exit if accessed directly
defined('ABSPATH') || exit;
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" <?= (bool) esc_attr(get_option('team_valid_auth')) ? '' : 'novalidate' ?>>
        <?php
            settings_fields($settings_page);
        ?>
        <input type="hidden" name="team_valid_auth" value="<?= esc_attr(get_option('team_valid_auth')); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Cheie API TeamCourier:</th>
                    <td><input type="text" name="team_key" value="<?= esc_attr(get_option('team_key')); ?>" size="50" /></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('team_valid_auth') ? 'hide' : '' ?>">
            <table class="form-table wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Setari curier</h4></th>
                        <td class="wc-shipping-class-slug"></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">Nume expeditor:</th>
                        <td><input type="text" name="team_name" value="<?= esc_attr(get_option('team_name')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Persoana de contact:</th>
                        <td><input type="text" name="team_contact_person" value="<?= esc_attr(get_option('team_contact_person')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Telefon expeditor:</th>
                        <td><input type="tel" name="team_phone" value="<?= esc_attr(get_option('team_phone')); ?>" size="50" required pattern="\+?\d{6,14}" /></td>
                    </tr>

                    <tr>
                        <th align="left">Email expeditor:</th>
                        <td><input type="email" name="team_email" value="<?= esc_attr(get_option('team_email')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Judet expeditor:</th>
                        <td><input type="text" name="team_county" value="<?= esc_attr(get_option('team_county')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Oras expeditor:</th>
                        <td><input type="text" name="team_city" value="<?= esc_attr(get_option('team_city')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Adresa expeditor:</th>
                        <td><input type="text" name="team_address" value="<?= esc_attr(get_option('team_address')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Cod Postal expeditor:</th>
                        <td><input type="text" name="team_postcode" value="<?= esc_attr(get_option('team_postcode')); ?>" size="50" required /></td>
                    </tr>

                    <tr>
                        <th align="left">Tip colet:</th>
                        <td>
                            <select name="team_package_type">
                                <option value="envelope" <?= esc_attr(get_option('team_package_type')) == 'envelope' ? 'selected="selected"' : ''; ?>>Plic</option>
                                <option value="package" <?= esc_attr(get_option('team_package_type')) == 'package' ? 'selected="selected"' : ''; ?>>Colet</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Serviciul implicit:</th>
                        <td>
                            <select name="team_service">
                            <?php
                                $services = CurieRO()->container->get(APITeamClass::class)->get_services();
                                $current_service = esc_attr(get_option('team_service'));
                                if (!empty($services)) {
                                    foreach($services as $service) {
                                        $selected = ($service['value'] == $current_service) ? 'selected="selected"' : '';
                                        echo "<option value='{$service['value']}' {$selected}>{$service['name']}</option>";
                                    }
                                } else {
                                    ?>
                                        <option value="Eco" <?= esc_attr(get_option('team_service')) == 'Eco' ? 'selected="selected"' : ''; ?>>Eco</option>
                                    <?php
                                }
                            ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur:</th>
                        <td>
                            <select name="team_retur">
                                <option value="true" <?= esc_attr(get_option('team_retur')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                                <option value="false" <?= esc_attr(get_option('team_retur')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                            </select>
                        </td>
                    </tr>

                    <tr id="tipRetur" <?= esc_attr(get_option('team_retur')) == 'false' ? 'style="display: none;"' : '' ?>>
                        <th align="left">Tip retur:</th>
                        <td>
                            <select name="team_retur_type">
                                <option value="document" <?= esc_attr(get_option('team_retur_type')) == 'document' ? 'selected="selected"' : ''; ?>>Document</option>
                                <option value="colet" <?= esc_attr(get_option('team_retur_type')) == 'colet' ? 'selected="selected"' : ''; ?>>Colet</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="team_parcel_count" value="<?= esc_attr(get_option('team_parcel_count')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Continut:</th>
                        <td><input type="text" name="team_content" value="<?= esc_attr(get_option('team_content')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Platitor expeditie:</th>
                        <td>
                            <select name="team_payer">
                                <option value="expeditor" <?= esc_attr(get_option('team_payer')) == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="destinatar" <?= esc_attr(get_option('team_payer')) == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip printare AWB:</th>
                        <td>
                            <select name="team_page_type">
                                <option value="default" <?= esc_attr( get_option('team_page_type') ) == 'default' ? 'selected="selected"' : ''; ?>>A5</option>
                                <option value="A6" <?= esc_attr( get_option('team_page_type') ) == 'A6' ? 'selected="selected"' : ''; ?>>A6</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="team_insurance">
                                <option value="0" <?= selected(0, get_option('team_insurance'), false) ?>>Nu</option>
                                <option value="1" <?= selected(1, get_option('team_insurance'), false) ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Deschidere colet:</th>
                        <td>
                            <select name="team_open_package">
                                <option value="false" <?= esc_attr(get_option('team_open_package')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_open_package')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare Sambata:</th>
                        <td>
                            <select name="team_sat_delivery">
                                <option value="false" <?= esc_attr(get_option('team_sat_delivery')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_sat_delivery')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Taxa urgent express:</th>
                        <td>
                            <select name="team_tax_urgent_express">
                                <option value="false" <?= esc_attr(get_option('team_tax_urgent_express')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_tax_urgent_express')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Schimbare adresa de livrare:</th>
                        <td>
                            <select name="team_change_delivery_address">
                                <option value="false" <?= esc_attr(get_option('team_change_delivery_address')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_change_delivery_address')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Ora speciala de livrare:</th>
                        <td>
                            <select name="team_special_delivery_hour">
                                <option value="false" <?= esc_attr(get_option('team_special_delivery_hour')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_special_delivery_hour')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Swap (colet la schimb):</th>
                        <td>
                            <select name="team_swap_package">
                                <option value="false" <?= esc_attr(get_option('team_swap_package')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_swap_package')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur confirmare de primire:</th>
                        <td>
                            <select name="team_retur_delivery_confirmation">
                                <option value="false" <?= esc_attr(get_option('team_retur_delivery_confirmation')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_retur_delivery_confirmation')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur documente:</th>
                        <td>
                            <select name="team_retur_documents">
                                <option value="false" <?= esc_attr(get_option('team_retur_documents')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_retur_documents')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">A 3-a livrare nationala:</th>
                        <td>
                            <select name="team_3rd_national_delivery">
                                <option value="false" <?= esc_attr(get_option('team_3rd_national_delivery')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_3rd_national_delivery')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Retur expediere/colet nelivrat:</th>
                        <td>
                            <select name="team_retur_expedition_undelivered_package">
                                <option value="false" <?= esc_attr(get_option('team_retur_expedition_undelivered_package')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_retur_expedition_undelivered_package')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Adaugare AWB agent TeamCourier:</th>
                        <td>
                            <select name="team_awb_by_delivery_agent">
                                <option value="false" <?= esc_attr(get_option('team_awb_by_delivery_agent')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_awb_by_delivery_agent')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Etichetare colet/plic la sediu TeamCourier:</th>
                        <td>
                            <select name="team_labeling_package_with_awb">
                                <option value="false" <?= esc_attr(get_option('team_labeling_package_with_awb')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_labeling_package_with_awb')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Colete aditionale:</th>
                        <td>
                            <select name="team_multiple_packages">
                                <option value="false" <?= esc_attr(get_option('team_multiple_packages')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_multiple_packages')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Colete fragile:</th>
                        <td>
                            <select name="team_is_fragile">
                                <option value="false" <?= esc_attr(get_option('team_is_fragile')) == 'false' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="true" <?= esc_attr(get_option('team_is_fragile')) == 'true' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="team_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('team_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('team_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text"  name="team_subiect_mail" value="<?= esc_attr(get_option('team_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="team_titlu_mail" value="<?= esc_attr(get_option('team_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                $email_template = get_option('team_email_template');
                                wp_editor( $email_template, 'team_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                            <select name="team_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('team_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('team_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="team_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('team_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('team_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !(bool) get_option('team_valid_auth') ? 'hide' : '' ?>">
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/team') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    api_key: $('input[name="team_key"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="team_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="team_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('select[name="team_retur"]').on('change', () => {
            this.value === 'true' ? $('#tipRetur').show() : $('#tipRetur').hide();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'team');
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

<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$innoship_api = CurieRO()->container->get(APIInnoshipClass::class);
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post" <?= (bool) esc_attr(get_option('innoship_valid_auth')) ? '' : 'novalidate' ?>>
        <?php
            settings_fields($settings_page);
        ?>
        <input type="hidden" name="innoship_valid_auth" value="<?= esc_attr(get_option('innoship_valid_auth')); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Cheie API Innoship:</th>
                    <td><input type="text" name="innoship_api_key" value="<?= esc_attr(get_option('innoship_api_key')); ?>" size="50" placeholder="Cheie API Innoship" /></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('innoship_valid_auth') ? 'hide' : '' ?>">
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
                            <select name="innoship_location_id">
                                <option value="">Alege o locatie</option>
                                <?php
                                if ((bool) get_option('innoship_valid_auth')) {
                                    $curiero_innoship_locations = $innoship_api->getClientLocations();
                                    foreach ($curiero_innoship_locations as $innoship_location) {
                                        echo "<option value='{$innoship_location['externalLocationId']}'" . selected($innoship_location['externalLocationId'], get_option('innoship_location_id'), false) . ">{$innoship_location['name']}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Curier implicit:</th>
                        <td>
                            <select name="innoship_courier_id">
                                <option value="">Selectie inteligenta a curierului prin Innoship</option>
                                <?php
                                if ((bool) get_option('innoship_valid_auth')) {
                                    $client_courier_list = $innoship_api->getClientCouriers();
                                    foreach ($client_courier_list as $client_courier_id => $client_courier) {
                                        echo "<option value='{$client_courier_id}'" . selected($client_courier_id, get_option('innoship_courier_id'), false) . ">{$client_courier}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Serviciul implicit:</th>
                        <td>
                            <select name="innoship_service_id">
                                <?php
                                if ((bool) esc_attr(get_option('innoship_valid_auth'))) {
                                    $current_service_id = esc_attr(get_option('innoship_service_id'));
                                    $all_services = $innoship_api::getClientServices();
                                    foreach ($all_services as $key => $service) {
                                        echo "<option " . selected($key, $current_service_id, false) . " value='{$key}'>{$service}</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Plata transport: </th>
                        <td>
                            <select name="innoship_delivery_payer">
                                <option value="sender" <?= esc_attr(get_option('innoship_delivery_payer')) == 'expeditor' ? 'selected="selected"' : ''; ?>>Expeditor</option>
                                <option value="receiver" <?= esc_attr(get_option('innoship_delivery_payer')) == 'destinatar' ? 'selected="selected"' : ''; ?>>Destinatar</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Colectarea rambursului: </th>
                        <td>
                            <select name="innoship_money_delivery_method">
                                <option value="bank" <?= esc_attr(get_option('innoship_money_delivery_method')) == 'bank' ? 'selected="selected"' : ''; ?>>Cont colector</option>
                                <option value="cash" <?= esc_attr(get_option('innoship_money_delivery_method')) == 'cash' ? 'selected="selected"' : ''; ?>>Cash</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Numar plicuri:</th>
                        <td><input type="number" name="innoship_envelope_no" value="<?= esc_attr(get_option('innoship_envelope_no')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Numar colete:</th>
                        <td><input type="number" name="innoship_package_no" value="<?= esc_attr(get_option('innoship_package_no')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Numar paleti:</th>
                        <td><input type="number" name="innoship_palette_no" value="<?= esc_attr(get_option('innoship_palette_no')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Tip ambalaj colet:</th>
                        <td><input type="text" name="innoship_package_type" value="<?= esc_attr(get_option('innoship_package_type')); ?>" size="50" placeholder="Ex: Carton" /></td>
                    </tr>

                    <tr>
                        <th align="left">Tip continut colet:</th>
                        <td><input type="text" name="innoship_package_contents" value="<?= esc_attr(get_option('innoship_package_contents')); ?>" size="50" placeholder="Ex: Obiecte de arta" /></td>
                    </tr>

                    <tr>
                        <th align="left">Tip printare AWB:</th>
                        <td>
                            <select name="innoship_page_type">
                                <option value="A6" <?= esc_attr(get_option('innoship_page_type')) == 'A6' ? 'selected="selected"' : ''; ?>>A6</option>
                                <option value="A4" <?= esc_attr(get_option('innoship_page_type')) == 'A4' ? 'selected="selected"' : ''; ?>>A4</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip observatii:</th>
                        <td>
                            <select name="innoship_observation_type">
                                <option value="nu" <?= esc_attr(get_option('innoship_observation_type')) == 'nu' ? 'selected="selected"' : ''; ?>>Fara observatii</option>
                                <option value="name" <?= esc_attr(get_option('innoship_observation_type')) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produse</option>
                                <option value="sku" <?= esc_attr(get_option('innoship_observation_type')) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produse</option>
                                <option value="both" <?= esc_attr(get_option('innoship_observation_type')) == 'both' ? 'selected="selected"' : ''; ?>>Denumire + SKU produse</option>
                                <option value="custom" <?= esc_attr(get_option('innoship_observation_type')) == 'custom' ? 'selected="selected"' : ''; ?>>Observatie generica completata mai jos</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Observatii:<br> <span class="letterCount">0</span>/500 caractere</th>
                        <td>
                            <textarea name="innoship_observation" lines="2" maxlength="500"><?= esc_attr(get_option('innoship_observation')) ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Greutate implicita:</th>
                        <td><input type="number" name="innoship_default_weight" value="<?= esc_attr(get_option('innoship_default_weight')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="innoship_declared_value">
                                <option value="0" <?= selected(0, get_option('innoship_declared_value'), false) ?>>Nu</option>
                                <option value="1" <?= selected(1, get_option('innoship_declared_value'), false) ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Deschidere la livrare:</th>
                        <td>
                            <select name="innoship_open_on_arrival">
                                <option value="nu" <?= esc_attr(get_option('innoship_open_on_arrival')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('innoship_open_on_arrival')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Livrare sambata:</th>
                        <td>
                            <select name="innoship_saturday_delivery">
                                <option value="nu" <?= esc_attr(get_option('innoship_saturday_delivery')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('innoship_saturday_delivery')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="innoship_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('innoship_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('innoship_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text" name="innoship_subiect_mail" value="<?= esc_attr(get_option('innoship_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="innoship_titlu_mail" value="<?= esc_attr(get_option('innoship_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                [total_comanda] - Reprezinta totalul de plata al comenzii.<br>
                                [innoship_denumire_curier] - Reprezinta denumirea curierului ales la generare.<br>
                                [innoship_link_urmarire] - Reprezinta link-ul de urmarire al AWB-ului.", true) ?>
                            </div>
                        </th>
                        <td>
                            <?php
                            $email_template = get_option('innoship_email_template');
                            wp_editor($email_template, 'innoship_email_template', $settings = array('textarea_rows' => '10', 'media_buttons' => false));
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
                            <select name="innoship_auto_generate_awb">
                                <option value="nu" <?= esc_attr(get_option('innoship_auto_generate_awb')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('innoship_auto_generate_awb')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="innoship_auto_mark_complete">
                                <option value="nu" <?= esc_attr(get_option('innoship_auto_mark_complete')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('innoship_auto_mark_complete')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !(bool) get_option('innoship_valid_auth') ? 'hide' : '' ?>">
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/innoship') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    api_key: $('input[name="innoship_api_key"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="innoship_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="innoship_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'innoship');
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

        const letterCountEl = document.querySelector('.letterCount');
        const innoshipObservationEl = document.querySelector('textarea[name="innoship_observation"]');

        letterCountEl.innerHTML = innoshipObservationEl.value.length;
        $(innoshipObservationEl).on('keyup change', () => letterCountEl.innerHTML = innoshipObservationEl.value.length);
    })
</script>
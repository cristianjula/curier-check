<?php
// Exit if accessed directly
defined('ABSPATH') || exit;

$sameday = CurieRO()->container->get(CurieroSamedayClass::class);
$samedayAPI = CurieRO()->container->get(APISamedayClass::class);
$services = (bool) get_option('sameday_valid_auth') ? $samedayAPI->getAdditionalServices() : collect();
?>

<link rel="stylesheet" href="<?= CURIERO_PLUGIN_URL ?>/assets/css/settings_page.min.css">

<div class="wrap">
    <h1>CurieRO - Setari <?= $courier_name ?></h1>
    <br>
    <form action="options.php" method="post">
        <?php
            settings_fields($settings_page);
        ?>
        <input type="hidden" name="sameday_valid_auth" value="<?= esc_attr(get_option('sameday_valid_auth')); ?>">
        <table class="form-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th class="wc-shipping-class-name"><h4 style="margin:5px 0">Credentiale</h4></th>
                    <td class="wc-shipping-class-slug"></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th align="left">Utilizator Sameday:</th>
                    <td><input type="text"  name="sameday_username" value="<?= esc_attr(get_option('sameday_username')); ?>" size="50" placeholder="Numele utilizatorului Sameday"/></td>
                </tr>

                <tr>
                    <th align="left">Parola Sameday:</th>
                    <td><input type="password"  name="sameday_password" value="<?= esc_attr(get_option('sameday_password')); ?>" size="50" placeholder="Parola utilizatorului Sameday"/></td>
                </tr>

                <tr>
                    <th align="left" class="validationResponse"></th>
                    <td align="right">
                        <button type="button" name="validate" class="button">Valideaza credentialele <?= $courier_name ?></button>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="<?= !(bool) get_option('sameday_valid_auth') ? 'hide' : '' ?>">
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
                            <select name="sameday_pickup_point">
                                <?php
                                    if ((bool) get_option('sameday_valid_auth')) {
                                        $pickup_points = get_transient('curiero_sameday_pickup_points');
                                        $current_pickup_point = get_option('sameday_pickup_point');
                                        if (empty($pickup_points)) {
                                            $pickup_points = json_decode($sameday->callMethod('pickup_points', [], 'GET')['message'], true);
                                            set_transient('curiero_sameday_pickup_points', $pickup_points, DAY_IN_SECONDS);
                                        }
                                        if (!empty($pickup_points)) {
                                            foreach($pickup_points as $pickup_point) {
                                                echo "<option value='{$pickup_point['id']}'". selected($pickup_point['id'], $current_pickup_point, false) .">{$pickup_point['name']}</option>";
                                            }
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip trimitere:</th>
                        <td>
                            <select name="sameday_package_type">
                                <option value="0" <?= esc_attr(get_option('sameday_package_type')) == '0' ? 'selected="selected"' : ''; ?>>Colet</option>
                                <option value="1" <?= esc_attr(get_option('sameday_package_type')) == '1' ? 'selected="selected"' : ''; ?>>Plic</option>
                                <option value="2" <?= esc_attr(get_option('sameday_package_type')) == '2' ? 'selected="selected"' : ''; ?>>Palet</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Serviciul implicit pentru livrari obisnuite:</th>
                        <td>
                            <select name="sameday_ord_service_id">
                                <?php
                                    if ((bool) get_option('sameday_valid_auth')) {
                                        $current_ord_service_id = esc_attr(get_option('sameday_ord_service_id'));
                                        $ord_services = $services->filter(function($service) {
                                            return stripos($service['name'], 'Locker') === false;
                                        });
                                        foreach($ord_services->sortByDesc('delivery.name') as $service)
                                            echo "<option ". selected($service['id'], $current_ord_service_id, false) ." value='{$service['id']}'>{$service['delivery']['name']} - {$service['name']}</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Servicii aditionale pentru livrari obisnuite:</th>
                        <td>
                            <select name="sameday_ord_additional_services[]" multiple></select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Serviciul implicit pentru livrari Easybox:</th>
                        <td>
                            <select name="sameday_locker_service_id">
                                <?php
                                    if ((bool) get_option('sameday_valid_auth')) {
                                        $current_locker_service_id = esc_attr(get_option('sameday_locker_service_id'));
                                        $locker_services = $services->filter(function($service) {
                                            return stripos($service['name'], 'Locker') !== false;
                                        });
                                        foreach($locker_services->sortByDesc('delivery.name') as $service)
                                            echo "<option ". selected($service['id'], $current_locker_service_id, false) ." value='{$service['id']}'>{$service['delivery']['name']} - {$service['name']}</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Servicii aditionale pentru livrari Easybox:</th>
                        <td>
                            <select name="sameday_locker_additional_services[]" multiple></select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Valoare asigurata:</th>
                        <td>
                            <select name="sameday_declared_value">
                                <option value="0" <?= selected(0, get_option('sameday_declared_value'), false) ?>>Nu</option>
                                <option value="1" <?= selected(1, get_option('sameday_declared_value'), false) ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Tip printare AWB:</th>
                        <td>
                            <select name="sameday_page_type">
                                <option value="A4" <?= esc_attr( get_option('sameday_page_type') ) == 'A4' ? 'selected="selected"' : ''; ?>>A4</option>
                                <option value="A6" <?= esc_attr( get_option('sameday_page_type') ) == 'A6' ? 'selected="selected"' : ''; ?>>A6</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Observatii:<br> (max 200 caractere):</th>
                        <td>
                            <textarea name="sameday_observation" lines="2" maxlength="200"><?= esc_attr( get_option('sameday_observation') ) ?></textarea>
                            <sub style="float:right;"><span class="letterCount">0</span>/200</sub>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Descriere continut:</th>
                        <td>
                            <select name="sameday_descriere_continut">
                                <option value="nu" <?= esc_attr( get_option('sameday_descriere_continut') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="name" <?= esc_attr( get_option('sameday_descriere_continut') ) == 'name' ? 'selected="selected"' : ''; ?>>Denumire produs</option>
                                <option value="sku" <?= esc_attr( get_option('sameday_descriere_continut') ) == 'sku' ? 'selected="selected"' : ''; ?>>SKU produs</option>
                                <option value="both" <?= esc_attr( get_option('sameday_descriere_continut') ) == 'both' ? 'selected="selected"' : ''; ?>>Denumire+SKU produs</option>
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
                        <td><input type="number" name="sameday_force_length" value="<?= esc_attr( get_option('sameday_force_length') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Latime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="sameday_force_width" value="<?= esc_attr( get_option('sameday_force_width') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Inaltime colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="sameday_force_height" value="<?= esc_attr( get_option('sameday_force_height') ); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">
                            <div class="has_help_tip">
                                Greutate colet standard:
                                <?= wc_help_tip("In cazul in care dimensiunile standard nu sunt completate, ele vor fi calculate automat in functie de parametrii configurati la nivel de produs.") ?>
                            </div>
                        </th>
                        <td><input type="number" name="sameday_force_weight" value="<?= esc_attr( get_option('sameday_force_weight') ); ?>" size="50" /></td>
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
                            <select name="sameday_trimite_mail">
                                <option value="nu" <?= esc_attr(get_option('sameday_trimite_mail')) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr(get_option('sameday_trimite_mail')) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th align="left">Subiect mail:</th>
                        <td><input type="text"  name="sameday_subiect_mail" value="<?= esc_attr(get_option('sameday_subiect_mail')); ?>" size="50" /></td>
                    </tr>

                    <tr>
                        <th align="left">Titlu antet mail:</th>
                        <td><input type="text" name="sameday_titlu_mail" value="<?= esc_attr(get_option('sameday_titlu_mail')); ?>" size="50" placeholder="Ex: Comanda expediata!"/></td>
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
                                [sameday_easybox] - Reprezinta numele easybox-ului ales.", true) ?>
                            </div>
                        </th>
                        <td>
                            <?php
                                $email_template = get_option('sameday_email_template');
                                wp_editor( $email_template, 'sameday_email_template', $settings = array('textarea_rows'=> '10', 'media_buttons' => false ) );
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
                            <select name="sameday_auto_generate_awb">
                                <option value="nu" <?= esc_attr( get_option('sameday_auto_generate_awb') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('sameday_auto_generate_awb') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
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
                            <select name="sameday_auto_mark_complete">
                                <option value="nu" <?= esc_attr( get_option('sameday_auto_mark_complete') ) == 'nu' ? 'selected="selected"' : ''; ?>>Nu</option>
                                <option value="da" <?= esc_attr( get_option('sameday_auto_mark_complete') ) == 'da' ? 'selected="selected"' : ''; ?>>Da</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="endtable">
            <tr class="<?= !(bool) get_option('sameday_valid_auth') ? 'hide' : '' ?>">
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
        const url = "<?= curiero_get_api_url('/v1/auth/validate/sameday') ?>";

        $('button[name="validate"]').on('click', async function () {
            const request = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    username: $('input[name="sameday_username"]').val(),
                    password: $('input[name="sameday_password"]').val(),
                }),
            });

            const { success } = await request.json();
            if (success){
                $('.validationResponse').text('Autentificare reusita.').css('color', '#34a934');
                $('input[name="sameday_valid_auth"]').val('1');
            } else {
                $('.validationResponse').text('Autentificare esuata.').css('color', '#f44336');
                $('input[name="sameday_valid_auth"]').val('0');
            }

            $('#submit').click();
        });

        $('button[name="reset_email_template"]').on('click', async function () {
            let confirmation = confirm("Sunteți sigur(ă) că doriți să resetați câmpurile de mail la valorile implicite?");
            if (!confirmation) return;

            const resetForm = new FormData();
            resetForm.append('courier', 'sameday');
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

        $('.letterCount').text($('textarea[name="sameday_observation"]').val().length);
        $('textarea[name="sameday_observation"]').on('keyup change', function(){$('.letterCount').text($(this).val().length)})

        const service_list = <?= $services ?>;

        const set_additional_services = function (additional_services)
        {
            $('select[name="'+additional_services+'"] option').remove();
            $('select[name="'+additional_services+'"]').val('');

            const default_service = additional_services.includes('ord') ? 'sameday_ord_service_id' : 'sameday_locker_service_id';

            const current_package_type = $('select[name="sameday_package_type"]').val(),
                current_service_id =  $('select[name="'+default_service+'"]').val(),
                current_service = service_list.find(service => service['id'] == current_service_id) || {},
                available_additional_services = Object.keys(current_service).length ? current_service['optional_taxes'].filter(tax => tax['package_type'] == current_package_type) : [];

            available_additional_services.forEach(service => {
                const new_option = new Option(service['name'], service['id']);
                $('select[name="'+additional_services+'"]').append(new_option);
            });

            $('select[name="'+additional_services+'"]').trigger('change');
        }

        const set_existing_services = function ()
        {
            const ord_service_ids = JSON.parse('<?= json_encode(get_option('sameday_ord_additional_services', [])) ?>') || [];
            if (typeof ord_service_ids !== typeof []) return;

            ord_service_ids.forEach(
                (id) => $('select[name="sameday_ord_additional_services[]"] option[value='+id+']').prop('selected', true)
            )
            $('select[name="sameday_ord_additional_services[]"]').trigger('change');

            const locker_service_ids = JSON.parse('<?= json_encode(get_option('sameday_locker_additional_services', [])) ?>') || [];
            if (typeof locker_service_ids !== typeof []) return;

            locker_service_ids.forEach(
                (id) => $('select[name="sameday_locker_additional_services[]"] option[value='+id+']').prop('selected', true)
            )
            $('select[name="sameday_locker_additional_services[]"]').trigger('change');
        }

        set_additional_services('sameday_ord_additional_services[]');
        set_additional_services('sameday_locker_additional_services[]');
        set_existing_services();

        $('select[name="sameday_package_type"]').on('change', function() {
            set_additional_services('sameday_ord_additional_services[]');
            set_additional_services('sameday_locker_additional_services[]');
        });

        $('select[name="sameday_ord_service_id"]').on('change', function() {
            set_additional_services('sameday_ord_additional_services[]');
        });

        $('select[name="sameday_locker_service_id"]').on('change', function() {
            set_additional_services('sameday_locker_additional_services[]');
        });
    })
</script>

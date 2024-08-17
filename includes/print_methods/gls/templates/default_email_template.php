<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$email_template_field = 'GLS_email_template';
$email_subject_field = 'GLS_subiect_mail';
$email_title_field = 'GLS_titlu_mail';

$default_template = 'Buna ziua,
<br>
Comanda dumneavoastra  cu numarul: [nr_comanda] din data de [data_comanda] a fost expediata prin serviciu de curierat GLS si este in curs de livrare.
<br>
Nota de transport (AWB-ul) are numarul [nr_awb] si poate fi urmarita aici: <a href="https://gls-group.eu/RO/ro/urmarire-colet?match=[nr_awb]" target="_blank">Status comanda</a>
<br>
Va felicitam pentru alegerea facuta si va asteptam cu drag sa va onoram si alte comenzi.
<br>
Detalii comanda:
<br>[tabel_produse]<br>';

add_option($email_template_field, $default_template, false);
add_option($email_subject_field, 'Comanda dumneavoastra a fost expediata!', false);
add_option($email_title_field, 'Comanda expediata!', false);

register_setting('gls_settings', $email_template_field);
register_setting('gls_settings', $email_subject_field);
register_setting('gls_settings', $email_title_field);

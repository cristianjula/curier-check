<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$email_template_field = 'bookurier_email_template';
$email_subject_field = 'bookurier_subiect_mail';
$email_title_field = 'bookurier_titlu_mail';

$default_template = 'Buna ziua,
<br>
Comanda dumneavoastra  cu numarul: [nr_comanda] din data de [data_comanda] a fost expediata prin serviciu de curierat Bookurier si este in curs de livrare.
<br>
Nota de transport (AWB-ul) are numarul [nr_awb] si poate fi urmarita aici: <a href="https://www.bookurier.ro/colete/AWB/track0.php" target="_blank">Status comanda</a>
<br>
Va felicitam pentru alegerea facuta si va asteptam cu drag sa va onoram si alte comenzi.
<br>
Detalii comanda:
<br>[tabel_produse]<br>';

add_option($email_template_field, $default_template, false);
add_option($email_subject_field, 'Comanda dumneavoastra a fost expediata!', false);
add_option($email_title_field, 'Comanda expediata!', false);

register_setting('bookurier_settings', $email_template_field);
register_setting('bookurier_settings', $email_subject_field);
register_setting('bookurier_settings', $email_title_field);

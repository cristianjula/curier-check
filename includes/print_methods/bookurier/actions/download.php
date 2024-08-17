<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awbnr = $order->get_meta(CurieRO_Printing_Bookurier::$awb_field, true);

if (empty($awbnr)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$awb_list = explode(',', $awbnr);
$pdfFile = new CurieRO\Clegginabox\PDFMerger\PDFMerger();

$pdfs = [];
foreach ($awb_list as $awb) {
    $params = ['awb' => $awb];

    $result = CurieRO()->container->get(CurieroBookurierClass::class)->callMethod('downloadAwb', $params, 'POST');
    $mesage = json_decode($result['message'], true);

    if ($result['status'] == 200 && $mesage['success']) {
        $pdfs[$awb]['tmpfile'] = tmpfile();
        $pdfs[$awb]['pdf'] = fwrite($pdfs[$awb]['tmpfile'], base64_decode($mesage['awb']));
    } else {
        wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
        exit;
    }
}

foreach ($pdfs as $pdf) {
    $pdfFile->addPDF(stream_get_meta_data($pdf['tmpfile'])['uri'], 'all');
}

$pdf = (string) $pdfFile->merge('string');
$filename = $awb_list[0] . '-awb-bookurier.pdf';

curiero_output_pdf($pdf, $filename);

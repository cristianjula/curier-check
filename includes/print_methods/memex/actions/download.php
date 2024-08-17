<?php

// Exit if accessed directly
defined('ABSPATH') || exit;

$order = curiero_get_order($order_id);
$awb = $order->get_meta(CurieRO_Printing_Memex::$awb_field, true);
if (empty($awb)) {
    wp_redirect($order->get_edit_order_url());
    exit;
}

$pdfFile = new CurieRO\Clegginabox\PDFMerger\PDFMerger();

$pdfs = [];
$parameters = [
    'getLabelRequest' => [
        'PackageNo' => [
            'string' => $awb,
        ],
        'LabelFormat' => esc_attr(get_option('memex_label_format')),
    ],
];

$result = CurieRO()->container->get(CurieroMemexClass::class)->callMethod('downloadAwb', $parameters, 'POST');

$index = 0;
if ($result['status'] == 200) {
    $messages = json_decode($result['message'], true);

    foreach ($messages as $message) {
        $pdfs[$index]['tmpfile'] = tmpfile();
        $pdfs[$index]['pdf'] = fwrite($pdfs[$index]['tmpfile'], base64_decode($message));
        ++$index;
    }
} else {
    wp_die("<b class='bad'>Eroare la tiparire: </b>" . $result['message'] ?? 'AWB-ul nu a putut fi gasit.');
    exit;
}

foreach ($pdfs as $pdf) {
    $pdfFile->addPDF(stream_get_meta_data($pdf['tmpfile'])['uri'], 'all');
}

$pdf = (string) $pdfFile->merge('string');
$filename = $awb . '-awb-ptt.pdf';

curiero_output_pdf($pdf, $filename);

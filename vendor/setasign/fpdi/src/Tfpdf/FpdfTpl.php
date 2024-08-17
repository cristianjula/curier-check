<?php

/**
 * This file is part of FPDI
 *
 * @package   setasign\Fpdi
 * @copyright Copyright (c) 2023 Setasign GmbH & Co. KG (https://www.setasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */
namespace CurieRO\setasign\Fpdi\Tfpdf;

use CurieRO\setasign\Fpdi\FpdfTplTrait;
/**
 * Class FpdfTpl
 *
 * We need to change some access levels and implement the setPageFormat() method to bring back compatibility to tFPDF.
 */
class FpdfTpl extends \CurieRO\tFPDF
{
    use FpdfTplTrait;
}

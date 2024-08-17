<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayGetAwbPdfRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for downloading an PDF for an existing AWB request.
 *
 * @package Sameday
 */
class SamedayGetAwbPdfResponse implements SamedayResponseInterface
{
    use SamedayResponseTrait;
    /**
     * SamedayGetAwbPdfResponse constructor.
     *
     * @param SamedayGetAwbPdfRequest $request
     * @param SamedayRawResponse $rawResponse
     */
    public function __construct(SamedayGetAwbPdfRequest $request, SamedayRawResponse $rawResponse)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
    }
    /**
     * @return string
     */
    public function getPdf()
    {
        return $this->rawResponse->getBody();
    }
}

<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayPutAwbCODAmountRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for updating an AWB's COD amount.
 *
 * @package Sameday
 */
class SamedayPutAwbCODAmountResponse implements SamedayResponseInterface
{
    use SamedayResponseTrait;
    /**
     * SamedayPutAwbCODAmountResponse constructor.
     *
     * @param SamedayPutAwbCODAmountRequest $request
     * @param SamedayRawResponse $rawResponse
     */
    public function __construct(SamedayPutAwbCODAmountRequest $request, SamedayRawResponse $rawResponse)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
    }
}

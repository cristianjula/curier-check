<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayDeleteAwbRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for delete AWB request.
 *
 * @package Sameday
 */
class SamedayDeleteAwbResponse implements SamedayResponseInterface
{
    use SamedayResponseTrait;
    /**
     * SamedayDeleteAwbResponse constructor.
     *
     * @param SamedayDeleteAwbRequest $request
     * @param SamedayRawResponse $rawResponse
     */
    public function __construct(SamedayDeleteAwbRequest $request, SamedayRawResponse $rawResponse)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
    }
}

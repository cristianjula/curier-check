<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayPutParcelSizeRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for updating a parcel size request.
 *
 * @package Sameday
 */
class SamedayPutParcelSizeResponse implements SamedayResponseInterface
{
    use SamedayResponseTrait;
    /**
     * SamedayPutParcelSizeResponse constructor.
     *
     * @param SamedayPutParcelSizeRequest $request
     * @param SamedayRawResponse $rawResponse
     */
    public function __construct(SamedayPutParcelSizeRequest $request, SamedayRawResponse $rawResponse)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
    }
}

<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayPostParcelRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for creating a new parcel for an existing AWB request.
 *
 * @package Sameday
 */
class SamedayPostParcelResponse implements SamedayResponseInterface
{
    use SamedayResponseTrait;
    /**
     * @var string
     */
    protected $parcelAwbNumber;
    /**
     * SamedayPostParcelResponse constructor.
     *
     * @param SamedayPostParcelRequest $request
     * @param SamedayRawResponse $rawResponse
     * @param string $parcelAwbNumber
     */
    public function __construct(SamedayPostParcelRequest $request, SamedayRawResponse $rawResponse, $parcelAwbNumber)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
        $this->parcelAwbNumber = $parcelAwbNumber;
    }
    /**
     * @return string
     */
    public function getParcelAwbNumber()
    {
        return $this->parcelAwbNumber;
    }
}

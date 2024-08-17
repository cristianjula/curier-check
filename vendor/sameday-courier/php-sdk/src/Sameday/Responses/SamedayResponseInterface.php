<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Requests\SamedayRequestInterface;
/**
 * Interface that encapsulates a request+raw response pair.
 *
 * @package Sameday
 */
interface SamedayResponseInterface
{
    /**
     * @return SamedayRequestInterface
     */
    public function getRequest();
    /**
     * @return SamedayRawResponse
     */
    public function getRawResponse();
}

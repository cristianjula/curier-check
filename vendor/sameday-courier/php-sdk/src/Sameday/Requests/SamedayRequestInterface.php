<?php

namespace CurieRO\Sameday\Requests;

use CurieRO\Sameday\Http\SamedayRequest;
/**
 * Interface that encapsulates building a HTTP request.
 *
 * @package Sameday
 */
interface SamedayRequestInterface
{
    /**
     * Build HTTP request to be sent.
     *
     * @return SamedayRequest
     */
    public function buildRequest();
}

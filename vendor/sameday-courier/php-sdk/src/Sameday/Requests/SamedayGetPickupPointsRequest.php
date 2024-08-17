<?php

namespace CurieRO\Sameday\Requests;

use CurieRO\Sameday\Http\SamedayRequest;
use CurieRO\Sameday\Requests\Traits\SamedayRequestPaginationTrait;
/**
 * Request to get pickup points list.
 *
 * @package Sameday
 */
class SamedayGetPickupPointsRequest implements SamedayPaginatedRequestInterface
{
    use SamedayRequestPaginationTrait;
    /**
     * @inheritdoc
     */
    public function buildRequest()
    {
        return new SamedayRequest(\true, 'GET', '/api/client/pickup-points', $this->buildPagination());
    }
}

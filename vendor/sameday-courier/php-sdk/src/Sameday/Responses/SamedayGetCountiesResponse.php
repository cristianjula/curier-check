<?php

namespace CurieRO\Sameday\Responses;

use CurieRO\Sameday\Http\SamedayRawResponse;
use CurieRO\Sameday\Objects\CountyObject;
use CurieRO\Sameday\Requests\SamedayGetCountiesRequest;
use CurieRO\Sameday\Responses\Traits\SamedayResponsePaginationTrait;
use CurieRO\Sameday\Responses\Traits\SamedayResponseTrait;
/**
 * Response for get counties request.
 *
 * @package Sameday
 */
class SamedayGetCountiesResponse implements SamedayPaginatedResponseInterface
{
    use SamedayResponsePaginationTrait;
    use SamedayResponseTrait;
    /**
     * @var CountyObject[]
     */
    protected $counties = [];
    /**
     * SamedayGetCountiesResponse constructor.
     *
     * @param SamedayGetCountiesRequest $request
     * @param SamedayRawResponse $rawResponse
     */
    public function __construct(SamedayGetCountiesRequest $request, SamedayRawResponse $rawResponse)
    {
        $this->request = $request;
        $this->rawResponse = $rawResponse;
        $json = json_decode($this->rawResponse->getBody(), \true);
        $this->parsePagination($this->request, $json);
        if (!$json) {
            // Empty response.
            return;
        }
        foreach ($json['data'] as $data) {
            $this->counties[] = new CountyObject($data['id'], $data['name'], $data['code']);
        }
    }
    /**
     * @return CountyObject[]
     */
    public function getCounties()
    {
        return $this->counties;
    }
}

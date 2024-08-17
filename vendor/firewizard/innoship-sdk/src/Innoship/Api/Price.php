<?php

namespace CurieRO\Innoship\Api;

use CurieRO\Innoship\Request\CreateOrder;
use CurieRO\Innoship\Response\Contract;
use CurieRO\Innoship\Traits\HasHttpClient;
class Price
{
    use HasHttpClient;
    public function get(CreateOrder $request): Contract
    {
        return $this->getClient()->post('Price', $request->data());
    }
}

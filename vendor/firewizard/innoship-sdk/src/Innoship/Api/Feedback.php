<?php

namespace CurieRO\Innoship\Api;

use CurieRO\Innoship\Response\Contract;
use CurieRO\Innoship\Traits\HasHttpClient;
class Feedback
{
    use HasHttpClient;
    public function get($from, $to): Contract
    {
        return $this->getClient()->get("Feedback/{$from}/{$to}");
    }
}

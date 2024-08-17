<?php

namespace CurieRO\Innoship\Traits;

use CurieRO\Innoship\Client;
trait HasHttpClient
{
    protected $client;
    public function __construct(string $apiKey, bool $skipSslVerification)
    {
        $this->client = new Client($apiKey);
        if ($skipSslVerification) {
            $this->client = $this->client->skipSslVerification();
        }
    }
    protected function getClient(): Client
    {
        return $this->client;
    }
}

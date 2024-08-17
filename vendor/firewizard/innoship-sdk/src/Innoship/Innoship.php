<?php

namespace CurieRO\Innoship;

class Innoship
{
    private $apiKey, $skipSslVerification;
    public function __construct(string $apiKey, bool $skipSslVerification = \false)
    {
        $this->apiKey = $apiKey;
        $this->skipSslVerification = $skipSslVerification;
    }
    public function courier(): Api\Courier
    {
        return new Api\Courier($this->apiKey, $this->skipSslVerification);
    }
    public function feedback(): Api\Feedback
    {
        return new Api\Feedback($this->apiKey, $this->skipSslVerification);
    }
    public function label(): Api\Label
    {
        return new Api\Label($this->apiKey, $this->skipSslVerification);
    }
    public function location(): Api\Location
    {
        return new Api\Location($this->apiKey, $this->skipSslVerification);
    }
    public function order(): Api\Order
    {
        return new Api\Order($this->apiKey, $this->skipSslVerification);
    }
    public function price(): Api\Price
    {
        return new Api\Price($this->apiKey, $this->skipSslVerification);
    }
    public function track(): Api\Track
    {
        return new Api\Track($this->apiKey, $this->skipSslVerification);
    }
}

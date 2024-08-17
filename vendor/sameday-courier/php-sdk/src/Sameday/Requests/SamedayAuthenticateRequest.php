<?php

namespace CurieRO\Sameday\Requests;

use CurieRO\Sameday\Http\RequestBodyUrlEncoded;
use CurieRO\Sameday\Http\SamedayRequest;
/**
 * Request for authentication.
 *
 * @package Sameday
 */
class SamedayAuthenticateRequest implements SamedayRequestInterface
{
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * SamedayAuthenticateRequest constructor.
     *
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    /**
     * @inheritdoc
     */
    public function buildRequest()
    {
        return new SamedayRequest(\false, 'POST', '/api/authenticate', [], new RequestBodyUrlEncoded(['remember_me' => \true]), ['X-Auth-Username' => $this->username, 'X-Auth-Password' => $this->password]);
    }
}

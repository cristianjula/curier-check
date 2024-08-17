<?php

namespace CurieRO\Sameday\Objects\Traits;

/**
 * Trait for object name.
 *
 * @package Sameday
 */
trait SamedayObjectCodeTrait
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

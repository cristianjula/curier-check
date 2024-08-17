<?php

namespace CurieRO\Sameday\Objects;

use CurieRO\Sameday\Objects\Traits\SamedayObjectCodeTrait;
use CurieRO\Sameday\Objects\Traits\SamedayObjectIdTrait;
use CurieRO\Sameday\Objects\Traits\SamedayObjectNameTrait;
/**
 * County.
 *
 * @package Sameday
 */
class CountyObject
{
    use SamedayObjectIdTrait;
    use SamedayObjectNameTrait;
    use SamedayObjectCodeTrait;
    /**
     * CountyObject constructor.
     *
     * @param int $id
     * @param string $name
     * @param string $code
     */
    public function __construct($id, $name, $code)
    {
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
    }
}

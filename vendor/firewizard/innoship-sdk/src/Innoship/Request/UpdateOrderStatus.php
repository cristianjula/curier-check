<?php

namespace CurieRO\Innoship\Request;

use CurieRO\Innoship\Traits\Setter;
class UpdateOrderStatus implements Contract
{
    use Setter;
    public function data(): array
    {
        return [];
    }
}

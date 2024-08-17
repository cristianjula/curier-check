<?php

namespace CurieRO\Innoship\Response;

class Response extends Contract
{
    public function isSuccessful(): bool
    {
        return \true;
    }
}

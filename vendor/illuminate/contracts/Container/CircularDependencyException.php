<?php

namespace CurieRO\Illuminate\Contracts\Container;

use Exception;
use CurieRO\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}

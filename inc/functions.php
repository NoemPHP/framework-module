<?php

declare(strict_types=1);

namespace Noem\Framework;

use Invoker\Invoker;
use Noem\Container\Container;
use Noem\Container\Provider;

function bootstrap(Provider ...$providers): callable
{
    $container = new Container(...$providers);
    $invoker = $container->get(Invoker::class);
    return function (callable ...$initializers) use ($invoker) {
        foreach ($initializers as $initializer) {
            $invoker->call($initializer);
        }
    };
}

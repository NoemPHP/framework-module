<?php

declare(strict_types=1);

namespace Noem\Framework;

use Invoker\InvokerInterface;
use Noem\Container\Container;
use Noem\Container\Provider;

function bootstrap(Provider ...$providers): callable
{
    $c = new Container(...$providers);
    $invoker = $c->get(InvokerInterface::class);
    ErrorHandler::init(...array_map(fn($id) => $c->get($id), $c->getIdsWithTag('exception-handler')));

    $c->get('state-machine')->trigger((object)['hello' => 'world']);

    return function (callable ...$initializers) use ($invoker) {
        foreach ($initializers as $initializer) {
            $invoker->call($initializer);
        }
    };
}

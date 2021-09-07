<?php

declare(strict_types=1);

namespace Noem\Framework;

use Dotenv\Dotenv;
use Invoker\InvokerInterface;
use Noem\Container\Container;
use Noem\Container\Provider;
use Noem\State\EventManager;

function bootstrap(Provider ...$providers): callable
{
    $dotenv = Dotenv::createImmutable(getcwd());
    $dotenv->safeLoad();
    $c = new Container(...$providers);
    $invoker = $c->get(InvokerInterface::class);
    ErrorHandler::init(...array_map(fn($id) => $c->get($id), $c->getIdsWithTag('exception-handler')));

    $stateMachine = $c->get('state-machine');
    $observer = $c->get('state-machine.observer');
    assert($observer instanceof EventManager);

    return function (array|callable $initializers) use ($stateMachine, $invoker, $observer) {
        if (!is_array($initializers)) {
            $initializers = [StateName::ON => $initializers];
        }
        foreach ($initializers as $state => $initializer) {
            $observer->addEnterStateHandler(
                $state,
                function () use ($invoker, $initializer) {
                    $invoker->call($initializer);
                }
            );
        }

        return function (?object $event = null) use ($stateMachine, $initializers) {
            $event = $event ?? (object)['hello' => 'world'];
            $stateMachine->trigger($event);
        };
    };
}

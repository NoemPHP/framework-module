<?php

declare(strict_types=1);

namespace Noem\Framework;

use Lium\EventDispatcher\EventDispatcher;
use Lium\EventDispatcher\ListenerProvider\DefaultListenerProvider;
use Noem\Container\Attribute\Description;
use Noem\Container\Attribute\Tag;
use Noem\Container\Attribute\Tagged;
use Noem\Container\Container;
use Noem\StateMachineModule\Attribute\State;
use Noem\StateMachineModule\Attribute\Transition;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface as Provider;
use Throwable;

return [
    'state.' . StateName::OFF =>
        #[State(name: StateName::OFF)]
        fn(): callable => state(),

    'state.' . StateName::ON =>
        #[State(name: StateName::ON, parallel: true)]
        fn(): callable => state(),

    'transition.off|on' =>
        #[Transition(from: 'off', to: 'on')]
        fn(): callable => fn(object $t) => !$t instanceof \Throwable,

    'example-listener' =>
        #[Tag('event-listener')]
        fn(): callable => function (\stdClass $event) {
            $hi = true;
        },

    Provider::class =>
        #[Description('The default listener provider that consumes all Listeners with the "event-listener" Tag')]
        fn(#[Tagged('event-listener')] callable ...$l) => new DefaultListenerProvider($l),

    EventDispatcherInterface::class =>
        #[Description('The global event dispatcher of the Noem Application')]
        fn(Provider $p) => new EventDispatcher($p),

    'state-machine.initial-state' => fn() => StateName::OFF,

    'framework.move-to-error-state-on-exceptions' =>
        #[Tag('exception-handler')]
        fn(Container $c) => function (Throwable $e) use ($c) {
            /**
             * Intentionally fetched lazily instead of using auto-wiring.
             * This allows potential errors during state-machine init to be processed normally.
             */
            try {
                $stateMachine = $c->get('state-machine');
                $stateMachine->trigger($e);
            } catch (Throwable $e) {
                // ...
            }
        },

    'state-machine.guard.off-to-on' => fn() => function (object $trigger): bool {
        return !$trigger instanceof \Throwable;
    }
];

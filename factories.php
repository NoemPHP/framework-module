<?php

declare(strict_types=1);

namespace Noem\Framework;

use Noem\Container\Attribute\Tag;
use Noem\Container\Attribute\Tagged;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\ListenerCollection;
use Yiisoft\EventDispatcher\Provider\Provider;

return [
    'example-listener' =>
        #[Tag('event-listener')]
        fn() => function (\stdClass $event) {
            echo __FILE__;
        },
    ListenerCollection::class => function (#[Tagged('event-listener')] callable ...$listeners) {
        $collection = new ListenerCollection();
        foreach ($listeners as $listener) {
            $collection = $collection->add($listener);
        }
        return $collection;
    },
    ListenerProviderInterface::class => fn(ListenerCollection $l) => new Provider($l),
    EventDispatcherInterface::class => fn(ListenerProviderInterface $p) => new Dispatcher($p)
];

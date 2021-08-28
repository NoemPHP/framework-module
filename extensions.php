<?php

declare(strict_types=1);

namespace Noem\Framework;

use Psr\Container\ContainerInterface;

return [
    'state-machine.graph' => function (array $graph, ContainerInterface $c) {
        return array_replace_recursive(
            $graph,
            [
                'off' => [
                    'transitions' => [
                        [
                            'target' => 'on',
                            'guard' => $c->get('state-machine.guard.off-to-on')
                        ],
                        [
                            'target' => 'error',
                            'guard' => function (\Throwable $exception): bool {
                                return true;
                            }
                        ]
                    ]
                ],
                'on' => [
                    'transitions' => [
                        [
                            'target' => 'error',
                            'guard' => function (\Throwable $exception): bool {
                                return true;
                            }
                        ]
                    ],
                    'parallel' => true
                ],
                'error' => [

                ]
            ]
        );
    }
];

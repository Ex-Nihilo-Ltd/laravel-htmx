<?php

return [
    'errors' => [
        'defaultHandling' => 'send:event', // 'full:page'
        'eventType' => [
            'show-notification' => [
                'message' => 'response:text',
                'type' => 'error',
            ],
        ],
        'statusOverrides' => [
            404 => [
                'type' => 'full:page',
            ],
            403 => [
                'eventType' => [
                    'show-notification' => [
                        'description' => 'exception:message',
                    ],
                ],
            ],
            'dev' => [
                '5xx' => [
                    'type' => 'full-page',
                ],
                '4xx' => [
                    'eventType' => [
                        'show-notification' => [
                            'description' => 'exception:message',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

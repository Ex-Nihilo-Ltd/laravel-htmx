<?php

return [
    'errors' => [
        'handling' => 'send:event', // 'full:page'
        'eventType' => [
            'show-notification' => [
                'message' => 'response:text',
                'type' => 'error',
            ],
        ],
        'statusOverrides' => [
            404 => [
                'handling' => 'full:page',
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
                    'handling' => 'full:page',
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

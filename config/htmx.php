<?php

return [
    "errors" => [
        "fullPageRerenderOnStatus" => [404],
        "customEventOnStatus" => [
            401 => [
                "event" => ["show-notification" => ["message" => "Unauthorized", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            402 => [
                "event" => ["show-notification" => ["message" => "Payment Required", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            403 => [
                "event" => ["show-notification" => ["message" => "Forbidden", "type" => "error"]],
                "useExceptionMessage" => true,
                "exceptionMessageKey" => ["show-notification", "description"],
            ],
            404 => [
                "event" => ["show-notification" => ["message" => "Not Found", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            419 => [
                "event" => ["show-notification" => ["message" => "Page Expired", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            429 => [
                "event" => ["show-notification" => ["message" => "Too Many Requests", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            500 => [
                "event" => ["show-notification" => ["message" => "Internal Server Error", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
            503 => [
                "event" => ["show-notification" => ["message" => "Service Unavailable", "type" => "error"]],
                "useExceptionMessage" => false,
            ],
        ],
    ]
];
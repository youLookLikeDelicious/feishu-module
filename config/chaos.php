<?php

return [
    'services' => [
        'browserless' => [
            'image' => 'ghcr.io/browserless/chromium',
            'ports' => ['3000:3000'],
            'environment' => [
                "CONCURRENT=5",
                "TOKEN=6R0W53R135510",
                "ALLOW_GET=true",
            ],
            'networks' => [
                'chaos'
            ]
        ]
    ]
];
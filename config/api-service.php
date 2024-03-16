<?php

return [
    'navigation' => [
        /**
         * @deprecated 3.2
         */
        'group' => [
            'token' => 'User',
        ],
        'token' => [
            'group' => 'User',
            'sort' => -1,
            'icon' => 'heroicon-o-key',
        ],
    ],
    'models' => [
        'token' => [
            //If need to add more security to the EndPoint set to true for token policy
            'enable_policy' => false,
        ],
    ],
    'route' => [
        'panel_prefix' => false,
        'use_resource_middlewares' => false,
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ],
];

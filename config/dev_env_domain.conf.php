<?php
    /**
     * 开发环境配置
     */
    return [
        'redis'      => [
            'bin' => ['host' => '{host}', 'port' => 6389, 'password' => '{password}'],
            'pay' => ['host' => '{host}', 'port' => 6389, 'password' => '{password}'],
            'mpr' => ['host' => '{host}', 'port' => 6389, 'password' => '{password}'],
        ],
        'memcached'  => [
            ['memcache.server1', 11211],
            ['memcache.server2', 11211],
            ['memcache.server3', 11211],
            ['memcache.server4', 11211],
        ],
        'getui'      => [
            'appID'        => '{x}',
            'appKey'       => '{x}',
            'appSecret'    => '{x}',
            'masterSecret' => '{x}',
            'host'         => '{x}',
        ],
        'membership' => [
            'filmsMemberSyncDelay' => 2592000,
            'filmsMemberSync'      => true,
            'qiyi'                 => [
                'sdk2' => [
                    'key'              => '{x}',
                    'partner'          => '{x}',
                    'productIdMonthly' => '{x}'
                ]
            ]
        ],
        'db'         => [
            'bftv' => [
                'connectionString' => 'mysql:host={host};port=3306;dbname={dbname}',
                'username'         => '{username}',
                'password'         => '{password}',
                'charset'          => 'utf8',
            ],
            'db1'  => [
                'connectionString' => 'mysql:host={host};port=3306;dbname={dbname}',
                'username'         => '{username}',
                'password'         => '{password}',
                'charset'          => 'utf8',
            ],
            'db2'  => [
                'connectionString' => 'mysql:host={host};port=3306;dbname={dbname}',
                'username'         => '{username}',
                'password'         => '{password}',
                'charset'          => 'utf8',
            ]
        ]
    ];
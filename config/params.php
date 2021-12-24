<?php

declare(strict_types=1);

use Doctrine\DBAL\Types\DateImmutableType;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\DateTimeTzImmutableType;
use Doctrine\DBAL\Types\TimeImmutableType;
use Doctrine\DBAL\Types\Types;
use Yii\DBAL\Types\BigIntType;
use Yii\DBAL\Types\BinaryType;
use Yii\DBAL\Types\EnumType;

return [
    'yii/dbal' => [
        'types' => [
            'addTypes' => [
                'enum' => EnumType::class,
            ],
            'overrideTypes' => [
                Types::BINARY => BinaryType::class,
                // ВАЖНО!!!
                // В PDO MySQL даже если использовать строковые BigInt, проблема с числами больше PHP_INT_MAX остаётся.
                // Для решения этой проблемы необходимо установить PDO::ATTR_EMULATE_PREPARES = false.
                Types::BIGINT => BigIntType::class,
                Types::DATE_MUTABLE => DateImmutableType::class,
                Types::TIME_MUTABLE => TimeImmutableType::class,
                Types::DATETIME_MUTABLE => DateTimeImmutableType::class,
                Types::DATETIMETZ_MUTABLE => DateTimeTzImmutableType::class,
            ]
        ],
        'connectionManager' => [
            'defaultConnection' => 'default',
            'connections' => [
                'default' => [
                    /** PSR-16 or PSR-6 */
                    'queryCache' => null,
                    /** SQL query logger. Definition of Psr\Log\LoggerInterface */
                    'queryLogger' => null,
                    'useNamedParameters' => false,
                    'dbal' => [
                        /**
                         * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#configuration
                         * 'url' => 'mysql://user:secret@localhost/mydb',
                         */
                    ],
                ],
            ],
        ],
    ],
];

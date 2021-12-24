<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection as DoctrineConnection;
use Psr\Container\ContainerInterface;
use Yii\DBAL\Connection;
use Yii\DBAL\ConnectionManager;
use Yii\DBAL\Contracts\ConnectionManagerInterface;

/**
 * @var array $params
 */
return [
    ConnectionManagerInterface::class => ConnectionManager::class,
    ConnectionManager::class => [
        'definition' => static function (ContainerInterface $container) use ($params) {
            return new ConnectionManager(
                $container,
                $params['yii/dbal']['types'],
                $params['yii/dbal']['connectionManager']
            );
        },
        'reset' => function (): void {
            /** @var ConnectionManagerInterface $this */
            $this->resetConnection();
        },
    ],

    Connection::class => static fn (ConnectionManager $connManager): Connection => $connManager->getConnection(),

    DoctrineConnection::class => static fn (Connection $conn): DoctrineConnection => $conn->doctrineConnection,
];

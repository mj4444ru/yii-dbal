<?php

declare(strict_types=1);

namespace Yii\DBAL\Contracts;

use Yii\DBAL\Connection;

interface ConnectionManagerInterface
{
    public function getConnection(string $name = null): Connection;

    public function resetConnection(string $name = null);
}

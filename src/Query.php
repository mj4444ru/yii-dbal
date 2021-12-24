<?php

namespace Yii\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder;

class Query
{
    protected readonly Connection $connection;

    private ?ExpressionBuilder $expr = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function expr(): ExpressionBuilder
    {
        return $this->expr ?? ($this->expr = new ExpressionBuilder($this->connection));
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->connection->createQueryBuilder();
    }
}

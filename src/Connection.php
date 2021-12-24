<?php

declare(strict_types=1);

namespace Yii\DBAL;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Exception as DoctrineException;
use InvalidArgumentException;
use Yii\DBAL\Expressions\ExpressionBuilder;
use Yii\DBAL\Expressions\TableName;

use function count;

class Connection
{
    public readonly DoctrineConnection $doctrineConnection;
    public readonly bool $useNamedParameters;

    public function __construct(DoctrineConnection $doctrineConnection, bool $useNamedParameters = false)
    {
        $this->doctrineConnection = $doctrineConnection;
        $this->useNamedParameters = $useNamedParameters;
    }

    public function createExpressionBuilder(): ExpressionBuilder
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new ExpressionBuilder($this->doctrineConnection->getDatabasePlatform(), $this->useNamedParameters);
    }

    public function delete(): void
    {
        //TODO:
    }

    /**
     * @param array<string, mixed> $values
     *
     * @throws DoctrineException
     */
    public function insert(string|TableName $table, array $values): int
    {
        $table = $table instanceof TableName ? $table : $this->quoteIdentifier($table);
        $columns = array_map(fn (string $name): string => $this->quoteSingleIdentifier($name), array_keys($values));
        $placeholders = rtrim(str_repeat('?,', count($values)), ',');

        $paramIndex = 0;
        $params = [];
        $types = [];
        foreach ($values as $value) {
            if ($value instanceof Parameter) {
                $params[$paramIndex] = $value->value;
                if ($value->type !== null) {
                    $types[$paramIndex] = $value->type;
                }
            } else {
                $params[$paramIndex] = $value;
            }
            $paramIndex++;
        }

        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', $columns), $placeholders);

        return $this->doctrineConnection->executeStatement($sql, $params, $types);
    }

    /**
     * @param list<string> $columns
     * @param list<list<mixed>> $rows
     *
     * @throws DoctrineException
     */
    public function batchInsert(string|TableName $table, array $columns, array $rows): int
    {
        if (count($columns) === 0) {
            throw new InvalidArgumentException('The $columns argument cannot be empty.');
        }
        if (count($rows) === 0) {
            return 0;
        }

        $table = $table instanceof TableName ? $table : $this->quoteIdentifier($table);
        $columns = array_map(fn (string $name): string => $this->quoteSingleIdentifier($name), $columns);
        $placeholders = rtrim(str_repeat('?,', count($columns)), ',');
        $placeholders = rtrim(str_repeat('(' . $placeholders . '),', count($rows)), ',');

        $paramIndex = 0;
        $params = [];
        $types = [];
        foreach ($rows as $rowId => $row) {
            if (count($row) !== count($columns)) {
                throw new InvalidArgumentException(
                    sprintf('The number of values in row "%s" is different from the number of columns.', $rowId)
                );
            }
            foreach ($row as $value) {
                if ($value instanceof Parameter) {
                    $params[$paramIndex] = $value->value;
                    if ($value->type !== null) {
                        $types[$paramIndex] = $value->type;
                    }
                } else {
                    $params[$paramIndex] = $value;
                }
                $paramIndex++;
            }
        }

        $sql = sprintf('INSERT INTO %s (%s) VALUES %s', $table, implode(',', $columns), $placeholders);

        return $this->doctrineConnection->executeStatement($sql, $params, $types);
    }

    public function quoteIdentifier(string $value): string
    {
        if ($value === '') {
            throw new InvalidArgumentException('The identifier cannot be an empty string.');
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->doctrineConnection->getDatabasePlatform()->quoteIdentifier($value);
    }

    public function quoteSingleIdentifier(string $value): string
    {
        if ($value === '') {
            throw new InvalidArgumentException('The identifier cannot be an empty string.');
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->doctrineConnection->getDatabasePlatform()->quoteSingleIdentifier($value);
    }

    public function update(): void
    {
        //TODO:
    }
}

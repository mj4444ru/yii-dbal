<?php

declare(strict_types=1);

namespace Yii\DBAL\Expressions;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use InvalidArgumentException;
use LogicException;
use Yii\DBAL\Contracts\ExpressionInterface;
use Yii\DBAL\Parameter;

use function count;
use function is_string;

final class ExpressionBuilder
{
    public const EQ = '=';
    public const GT = '>';
    public const GTE = '>=';
    public const LT = '<';
    public const LTE = '<=';
    public const NEQ = '<>';

    public readonly bool $useNamedParameters;

    private int $boundCounter = 0;
    private readonly AbstractPlatform $platform;


    public function __construct(AbstractPlatform $platform, bool $useNamedParameters = null)
    {
        $this->platform = $platform;
        $this->useNamedParameters = $useNamedParameters;
    }

    public function and(
        string|ExpressionInterface $expression,
        string|ExpressionInterface ...$expressions
    ): CompositeExpression {
        return CompositeExpression::and($expression, ...$expressions);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function comparison(
        string $column,
        string $operator,
        mixed $value,
        int $valueType = null
    ): Expression {
        $column = $this->quoteIdentifier($column);

        if ($value instanceof ExpressionInterface) {
            return new Expression($column . ' ' . $operator . ' (' . $value . ')', $value->getParams());
        }

        $param = $this->createParameter($value, $valueType);

        return new Expression($column . ' ' . $operator . ' ' . $param, $param->getParams());
    }

    public function comparisonColumns(string $columnA, string $operator, string $columnB): Expression
    {
        $columnA = $this->quoteIdentifier($columnA);
        $columnB = $this->quoteIdentifier($columnB);
        return new Expression($columnA . ' ' . $operator . ' ' . $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function comparisonSqlFragment(
        ExpressionInterface $sqlFragment,
        string $operator,
        mixed $value,
        int $valueType = null
    ): Expression {
        if ($value instanceof ExpressionInterface) {
            return new Expression(
                '(' . $sqlFragment . ') ' . $operator . ' (' . $value . ')',
                array_merge($sqlFragment->getParams(), $value->getParams())
            );
        }

        $param = $this->createParameter($value, $valueType);

        return new Expression(
            '(' . $sqlFragment . ') ' . $operator . ' ' . $param,
            array_merge($sqlFragment->getParams(), $param->getParams())
        );
    }

    /**
     * @psalm-param ParameterType::*|null $type
     */
    public function createParameter(
        mixed $value,
        int $type = null,
        string $placeHolder = null
    ): Expression {
        if ($value instanceof ExpressionInterface) {
            throw new LogicException('The SqlFragmentInterface value cannot be used as '
                . 'a parameter for the ExpressionBuilder.');
        }

        $param = $type === null || ($value instanceof Parameter) ? $value : new Parameter($value, $type);

        if ($this->useNamedParameters) {
            $placeHolder = $placeHolder ? ':' . ltrim($placeHolder, ':') : $this->makeNamedPlaceHolder();

            return new Expression($placeHolder, [$placeHolder => $param]);
        }

        return new Expression('?', [$param]);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function eq(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::EQ, $value, $valueType);
    }

    public function eqColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::EQ, $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function gt(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::GT, $value, $valueType);
    }

    public function gtColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::GT, $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function gte(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::GTE, $value, $valueType);
    }

    public function gteColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::GTE, $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function in(
        string|ExpressionInterface $columnOrSqlFragment,
        array $values,
        int $valueType = null
    ): Expression {
        if (count($values) === 1) {
            $value = reset($values);
            $inValues = $value instanceof ExpressionInterface ? $value : $this->createParameter($value, $valueType);
        } else {
            $inValues = $this->convertInValuesToSqlFragment($values, $valueType);
        }

        return is_string($columnOrSqlFragment)
            ? $this->comparison($columnOrSqlFragment, 'IN', $inValues)
            : $this->comparisonSqlFragment($columnOrSqlFragment, 'IN', $inValues);
    }

    public function isNotNull(string|ExpressionInterface $columnOrSqlFragment): Expression
    {
        if (is_string($columnOrSqlFragment)) {
            return new Expression($this->quoteIdentifier($columnOrSqlFragment) . ' IS NOT NULL');
        }

        return new Expression('(' . $columnOrSqlFragment . ') IS NOT NULL', $columnOrSqlFragment->getParams());
    }

    public function isNull(string|ExpressionInterface $columnOrSqlFragment): Expression
    {
        if (is_string($columnOrSqlFragment)) {
            return new Expression($this->quoteIdentifier($columnOrSqlFragment) . ' IS NULL');
        }

        return new Expression('(' . $columnOrSqlFragment . ') IS NULL', $columnOrSqlFragment->getParams());
    }

    public function like(
        string|ExpressionInterface $columnOrSqlFragment,
        string $pattern,
        string $escapeChar = null
    ): Expression {
        $rawString = is_string($columnOrSqlFragment)
            ? $this->comparison($columnOrSqlFragment, 'LIKE', $pattern)
            : $this->comparisonSqlFragment($columnOrSqlFragment, 'LIKE', $pattern);

        return $escapeChar !== null
            ? new Expression($rawString . sprintf(' ESCAPE %s', $escapeChar), $rawString->getParams())
            : $rawString;
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function lt(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::LT, $value, $valueType);
    }

    public function ltColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::LT, $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function lte(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::LTE, $value, $valueType);
    }

    public function lteColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::LTE, $columnB);
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function neq(string $column, mixed $value, int $valueType = null): Expression
    {
        return $this->comparison($column, self::NEQ, $value, $valueType);
    }

    public function neqColumns(string $columnA, string $columnB): Expression
    {
        return $this->comparisonColumns($columnA, self::NEQ, $columnB);
    }

    public function not(string|ExpressionInterface $columnOrSqlFragment): Expression
    {
        if (is_string($columnOrSqlFragment)) {
            return new Expression('NOT ' . $this->quoteIdentifier($columnOrSqlFragment));
        }

        return new Expression('NOT (' . $columnOrSqlFragment . ')', $columnOrSqlFragment->getParams());
    }

    /**
     * @psalm-param ParameterType::*|null $valueType
     */
    public function notIn(
        string|ExpressionInterface $columnOrSqlFragment,
        array $values,
        int $valueType = null
    ): Expression {
        $inValues = count($values) === 1
            ? $this->createParameter(reset($values), $valueType)
            : $this->convertInValuesToSqlFragment($values, $valueType);

        return is_string($columnOrSqlFragment)
            ? $this->comparison($columnOrSqlFragment, 'NOT IN', $inValues)
            : $this->comparisonSqlFragment($columnOrSqlFragment, 'NOT IN', $inValues);
    }

    public function notLike(
        string|ExpressionInterface $columnOrSqlFragment,
        string $pattern,
        string $escapeChar = null
    ): Expression {
        $rawString = is_string($columnOrSqlFragment)
            ? $this->comparison($columnOrSqlFragment, 'NOT LIKE', $pattern)
            : $this->comparisonSqlFragment($columnOrSqlFragment, 'NOT LIKE', $pattern);

        return $escapeChar !== null
            ? new Expression($rawString . sprintf(' ESCAPE %s', $escapeChar), $rawString->getParams())
            : $rawString;
    }

    public function or(
        string|ExpressionInterface $expression,
        string|ExpressionInterface ...$expressions
    ): CompositeExpression {
        return CompositeExpression::or($expression, ...$expressions);
    }

    public function quoteIdentifier(string $value): string
    {
        if ($value === '') {
            throw new InvalidArgumentException('The identifier cannot be an empty string.');
        }

        return $this->platform->quoteIdentifier($value);
    }

    public function raw(string $rawString, array $params = []): Expression
    {
        return new Expression($rawString, $params);
    }

    public function rawTableName(string $table, string $database = null): TableName
    {
        $table = $this->platform->quoteSingleIdentifier($table);
        if ($database) {
            $table = $this->platform->quoteSingleIdentifier($database) . '.' . $table;
        }

        return new TableName($table);
    }

    private function convertInValuesToSqlFragment(array $values, ?int $valueType): Expression
    {
        $values = array_filter(array_unique($values));
        if (!$values) {
            throw new LogicException('The array of values for the IN operator cannot be empty.');
        }

        $params = [];
        if ($this->useNamedParameters) {
            $n = [];
            foreach ($values as $value) {
                if ($value instanceof ExpressionInterface) {
                    throw new LogicException('Only one SqlFragmentInterface value can be passed to the IN operator.');
                }
                $placeHolder = $this->makeNamedPlaceHolder();
                $n[] = $placeHolder;
                $params[$placeHolder] = ($value instanceof Parameter) ? $value : new Parameter($value, $valueType);
            }
            $names = implode(',', $n);
        } else {
            foreach ($values as $value) {
                if ($value instanceof ExpressionInterface) {
                    throw new LogicException('Only one SqlFragmentInterface value can be passed to the IN operator.');
                }
                $params[] = $valueType === null || ($value instanceof Parameter)
                    ? $value : new Parameter($value, $valueType);
            }
            $names = str_repeat('?,', count($values) - 1) . '?';
        }

        return new Expression($names, $params);
    }

    private function makeNamedPlaceHolder(): string
    {
        return ':yebValue' . ($this->boundCounter++);
    }
}

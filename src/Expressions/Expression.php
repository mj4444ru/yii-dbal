<?php

declare(strict_types=1);

namespace Yii\DBAL\Expressions;

use Yii\DBAL\Contracts\ExpressionInterface;

use function count;

final class Expression implements ExpressionInterface
{
    public function __construct(private string $rawString, private array $params = [])
    {
    }

    public function __toString(): string
    {
        return $this->rawString;
    }

    public function count(): int
    {
        return count($this->params);
    }

    public function getParams(): array
    {
        return $this->params;
    }
}

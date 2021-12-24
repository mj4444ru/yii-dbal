<?php

namespace Yii\DBAL\Expressions;

/**
 * @internal
 */
final class TableName
{
    public function __construct(public readonly string $rawName)
    {
    }

    public function __toString(): string
    {
        return $this->rawName;
    }
}

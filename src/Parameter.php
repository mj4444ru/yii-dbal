<?php

declare(strict_types=1);

namespace Yii\DBAL;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Type;

final class Parameter
{
    /**
     * @psalm-param  ParameterType::*|string|Type|null $type
     */
    public function __construct(public readonly mixed $value, public readonly int|string|Type|null $type = null)
    {
    }
}

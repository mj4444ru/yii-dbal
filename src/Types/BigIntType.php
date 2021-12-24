<?php

namespace Yii\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\Types;

/**
 * Type that maps a database BIGINT to a PHP integer.
 */
class BigIntType extends IntegerType
{
    public function getName(): string
    {
        return Types::BIGINT;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBigIntTypeDeclarationSQL($column);
    }
}

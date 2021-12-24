<?php

declare(strict_types=1);

namespace Yii\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * Type that maps ab SQL BINARY/VARBINARY to a PHP string.
 */
final class BinaryType extends Type
{
    public function getName(): string
    {
        return Types::BINARY;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getBinaryTypeDeclarationSQL($column);
    }
}

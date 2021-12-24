<?php

declare(strict_types=1);

namespace Yii\DBAL\Expressions;

use LogicException;
use Yii\DBAL\Contracts\ExpressionInterface;

use function count;

/**
 * @psalm-immutable
 */
final class CompositeExpression implements ExpressionInterface
{
    public const TYPE_AND = 'AND';
    public const TYPE_OR = 'OR';

    public readonly string $type;

    /**
     * @var list<string|ExpressionInterface>
     */
    private readonly array $parts;

    public function __toString(): string
    {
        if (count($this->parts) === 0) {
            return '';
        }
        if (count($this->parts) === 1) {
            return (string)$this->parts[0];
        }

        return '(' . implode(') ' . $this->type . ' (', $this->parts) . ')';
    }

    public static function and(string|ExpressionInterface ...$parts): self
    {
        return new self(self::TYPE_AND, ...$parts);
    }

    public function count(): int
    {
        return count($this->parts);
    }

    public function getParams(): array
    {
        $arrays = [];
        foreach ($this->parts as $part) {
            if ($part instanceof ExpressionInterface) {
                $arrays[] = $part->getParams();
            }
        }

        return array_merge($arrays);
    }

    public static function or(string|ExpressionInterface ...$parts): self
    {
        return new self(self::TYPE_OR, ...$parts);
    }

    public function with(string|ExpressionInterface ...$parts): self
    {
        $this->checkEmpty($parts);
        if (count($parts)) {
            if (count($this->parts) !== 0) {
                $parts = array_merge($this->parts, $parts);
            }

            return new self($this->type, ...$parts);
        }

        return $this;
    }

    private function __construct(string $type, string|ExpressionInterface ...$parts)
    {
        $this->type = $type;
        $this->parts = $parts;

        $this->checkEmpty($parts);
    }

    /**
     * @param array<string|ExpressionInterface|self> $parts
     */
    private function checkEmpty(array $parts): void
    {
        foreach ($parts as $part) {
            if ($part instanceof self) {
                if (count($part->parts) === 0) {
                    throw new LogicException('CompositeExpression cannot contain empty elements.');
                }
            } elseif (trim((string)$part) === '') {
                throw new LogicException('CompositeExpression cannot contain empty elements.');
            }
        }
    }
}

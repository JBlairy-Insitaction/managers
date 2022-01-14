<?php

namespace App\DBAL;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

abstract class EnumType extends Type
{
    protected string $name;
    /** @var string[] */
    protected array $values = [];

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = array_map(function ($val) { return "'" . $val . "'"; }, $this->values);

        return 'ENUM(' . implode(', ', $values) . ')';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, $this->values, true)) {
            throw new InvalidArgumentException("Invalid '" . $this->name . "' value.");
        }

        return $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /** @return string[] */
    public function getEnum(): array
    {
        return $this->values;
    }
}

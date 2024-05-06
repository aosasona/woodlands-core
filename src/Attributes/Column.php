<?php

declare(strict_types=1);

namespace Woodlands\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string $name,
        public ?array $encoder = null,
        public ?array $decoder = null,

        // If the base type is non-nullable but the actual column is nullable, this serves as a sort of override, the value is set to the `default` value if this is set to true, the base column is nullable and the default is set here
        public bool $nullable = false,
        public mixed $default = null,
        public bool $baseTypeIsNullable = false,
        public bool $hideFromOutput = false,
    ) {
    }
}

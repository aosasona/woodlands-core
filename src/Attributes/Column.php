<?php

namespace Woodlands\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string $name,
        public ?array $converter = null,
        public bool $nullable = false,
        public mixed $default = null,
    ) {
    }
}

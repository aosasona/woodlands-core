<?php

declare(strict_types=1);

namespace Woodlands\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Relationship
{
    public const SINGLE = "SINGLE";
    public const MANY = "MANY";

    public function __construct(
        public mixed $model,
        public string $property, // property in current model that links to foreign key
        public string $parentColumn, // foreign key column in the related model
        public string $cardinality = self::SINGLE,
    ) {
    }
}

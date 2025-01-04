<?php

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute for describing route parameters that should be resolved from the query string
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class QueryString
{
    /**
     * @param string|null $name The optional name of the query string parameter to resolve the value from
     */
    public function __construct(public readonly ?string $name = null) {}
}

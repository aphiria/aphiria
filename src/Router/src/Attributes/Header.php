<?php

namespace Aphiria\Routing\Attributes;

use Attribute;

/**
 * Defines the attribute for describing route parameters that should be resolved from the headers
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Header
{
    /**
     * @param string|null $name The optional name of the header to resolve the value from
     */
    public function __construct(public readonly ?string $name = null) {}
}

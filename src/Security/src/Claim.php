<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Security;

/**
 * Defines a statement about a subject
 *
 * @template T
 */
class Claim
{
    /** @var string The type of claim this is */
    public readonly string $type;

    /**
     * @param ClaimType|string $type The type of claim this is
     * @param T $value The value of the claim
     * @param string $issuer The issuer of the claim
     */
    public function __construct(
        ClaimType|string $type,
        public readonly mixed $value,
        public readonly string $issuer
    ) {
        if ($type instanceof ClaimType) {
            $this->type = $type->value;
        } else {
            $this->type = $type;
        }
    }
}

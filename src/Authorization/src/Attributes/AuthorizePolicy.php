<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Attributes;

use Aphiria\Authorization\Middleware\Authorize;
use Aphiria\Middleware\Attributes\Middleware;
use Attribute;

/**
 * Defines the attribute used for requiring authorization policies
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class AuthorizePolicy extends Middleware
{
    /**
     * @param string $policyName The name of the policy to use for authorization
     */
    public function __construct(string $policyName)
    {
        parent::__construct(Authorize::class, ['policyName' => $policyName]);
    }
}

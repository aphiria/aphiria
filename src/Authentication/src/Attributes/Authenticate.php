<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Attributes;

use Aphiria\Authentication\Middleware\Authenticate as AuthenticateMiddleware;
use Aphiria\Middleware\Attributes\Middleware;
use Attribute;

/**
 * Defines the authentication middleware
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Authenticate extends Middleware
{
    /**
     * @param string|null $schemeName The name of the authentication scheme to use, or null if using the default scheme
     */
    public function __construct(string $schemeName = null)
    {
        parent::__construct(AuthenticateMiddleware::class, ['schemeName' => $schemeName]);
    }
}

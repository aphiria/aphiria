<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Tests\Mocks;

use Aphiria\Authentication\AuthenticationResult;
use Aphiria\Security\IPrincipal;
use Exception;

/**
 * Defines a mock authentication result that elevates the protected constructor in the base class for testing
 */
readonly class MockAuthenticationResult extends AuthenticationResult
{
    public function __construct(
        bool $passed,
        string $schemeName,
        ?IPrincipal $user = null,
        ?Exception $failure = null
    ) {
        parent::__construct($passed, $schemeName, $user, $failure);
    }
}

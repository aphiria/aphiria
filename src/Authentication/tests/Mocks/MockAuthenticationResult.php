<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
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
    /**
     * @param bool $passed
     * @param list<string>|string $schemeNames
     * @param IPrincipal|null $user
     * @param Exception|null $failure
     */
    public function __construct(
        bool $passed,
        array|string $schemeNames,
        ?IPrincipal $user = null,
        ?Exception $failure = null
    ) {
        parent::__construct($passed, $schemeNames, $user, $failure);
    }
}

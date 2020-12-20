<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes\Mocks;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;

/**
 * Mocks a route constraint for use in testing
 */
final class DummyConstraint implements IRouteConstraint
{
    /**
     * @param string $constructorParam A dummy parameter to test that constraints' constructor params are passed in
     */
    public function __construct(private string $constructorParam)
    {
    }

    /**
     * @inheritdoc
     */
    public function passes(MatchedRouteCandidate $matchedRouteCandidate, string $httpMethod, string $host, string $path, array $headers): bool
    {
        return true;
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations\Mocks;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;

/**
 * Mocks a route constraint for use in testing
 */
final class DummyConstraint implements IRouteConstraint
{
    private string $constructorParam;

    /**
     * @param string $constructorParam A dummy parameter to test that constraints' constructor params are passed in
     */
    public function __construct(string $constructorParam)
    {
        $this->constructorParam = $constructorParam;
    }

    /**
     * @inheritdoc
     */
    public function passes(MatchedRouteCandidate $matchedRouteCandidate, string $httpMethod, string $host, string $path, array $headers): bool
    {
        return true;
    }
}

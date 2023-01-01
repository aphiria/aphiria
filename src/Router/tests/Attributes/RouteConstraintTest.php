<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\RouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\MatchedRouteCandidate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RouteConstraintTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        /**
         * @psalm-suppress UndefinedClass Intentionally testing an empty string
         * @psalm-suppress ArgumentTypeCoercion Ditto
         */
        new RouteConstraint('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $constraint = new class () implements IRouteConstraint {
            public function passes(MatchedRouteCandidate $matchedRouteCandidate, string $httpMethod, string $host, string $path, array $headers): bool
            {
                return true;
            }
        };
        $constraintAttribute = new RouteConstraint($constraint::class, ['bar']);
        $this->assertSame($constraint::class, $constraintAttribute->className);
        $this->assertSame(['bar'], $constraintAttribute->constructorParameters);
    }
}

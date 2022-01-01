<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Constraints;

use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use Aphiria\Routing\UriTemplates\Constraints\RouteVariableConstraintFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RouteVariableConstraintFactoryTest extends TestCase
{
    private RouteVariableConstraintFactory $constraintFactory;

    protected function setUp(): void
    {
        $this->constraintFactory = new RouteVariableConstraintFactory();
    }

    public function testClosureThatDoesNotReturnConstraintInstanceThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Factory for constraint "foo" does not return an instance of ' . IRouteVariableConstraint::class
        );
        /** @psalm-suppress InvalidArgument We're specifically testing the types at runtime */
        $this->constraintFactory->registerConstraintFactory('foo', fn (): array => []);
        $this->constraintFactory->createConstraint('foo');
    }

    public function testCreatingConstraintWithNoFactoryRegisteredThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No factory registered for constraint "foo"');
        $this->constraintFactory->createConstraint('foo');
    }

    public function testFactoryThatDoesNotTakeParametersReturnsConstraintInstance(): void
    {
        $expectedConstraint = $this->createMock(IRouteVariableConstraint::class);
        $factory = fn (): IRouteVariableConstraint => $expectedConstraint;
        $this->constraintFactory->registerConstraintFactory('foo', $factory);
        $this->assertSame($expectedConstraint, $this->constraintFactory->createConstraint('foo'));
    }

    public function testFactoryThatTakesParametersReturnsConstraintInstance(): void
    {
        $expectedConstraint = $this->createMock(IRouteVariableConstraint::class);
        $factory = function (int $foo, int $bar) use ($expectedConstraint): IRouteVariableConstraint {
            $this->assertSame(1, $foo);
            $this->assertSame(2, $bar);

            return $expectedConstraint;
        };
        $this->constraintFactory->registerConstraintFactory('foo', $factory);
        $this->assertSame($expectedConstraint, $this->constraintFactory->createConstraint('foo', [1, 2]));
    }
}

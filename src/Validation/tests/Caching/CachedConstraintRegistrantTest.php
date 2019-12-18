<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Caching;

use Aphiria\Validation\Caching\CachedConstraintRegistrant;
use Aphiria\Validation\Caching\IConstraintRegistryCache;
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use Aphiria\Validation\IConstraintRegistrant;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the cached constraint registrant
 */
class CachedConstraintRegistrantTest extends TestCase
{
    public function testRegisteringConstraintsWillIncludeConstraintsInInitialRegistrant(): void
    {
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IValidationConstraint::class);
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $initialConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IValidationConstraint $propConstraint;

            public function __construct(string $className, string $propName, IValidationConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ConstraintRegistry $constraints): void
            {
                $constraints->registerPropertyConstraints(
                    $this->className,
                    $this->propName,
                    $this->propConstraint
                );
            }
        };
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache, $initialConstraintRegistrant);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant->registerConstraints($constraints);
        $this->assertSame([$propConstraint], $constraints->getPropertyConstraints($className, $propName));
    }

    public function testRegisteringConstraintsWillIncludeConstraintsInAddedRegistrant(): void
    {
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IValidationConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IValidationConstraint $propConstraint;

            public function __construct(string $className, string $propName, IValidationConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ConstraintRegistry $constraints): void
            {
                $constraints->registerPropertyConstraints(
                    $this->className,
                    $this->propName,
                    $this->propConstraint
                );
            }
        };
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant->registerConstraints($constraints);
        $this->assertSame([$propConstraint], $constraints->getPropertyConstraints($className, $propName));
    }

    public function testRegisteringConstraintsWithCacheThatHitsReturnsThoseConstraints(): void
    {
        /** @var IConstraintRegistryCache|MockObject $constraintRegistryCache */
        $expectedConstraints = new ConstraintRegistry();
        $expectedConstraints->registerPropertyConstraints('foo', 'prop', $this->createMock(IValidationConstraint::class));
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedConstraints);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->registerConstraints($constraints);
        $this->assertCount(1, $constraints->getPropertyConstraints('foo', 'prop'));
    }

    public function testRegisteringConstraintsWithCacheThatMissesStillRunsTheRegistrants(): void
    {
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IValidationConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IValidationConstraint $propConstraint;

            public function __construct(string $className, string $propName, IValidationConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ConstraintRegistry $constraints): void
            {
                $constraints->registerPropertyConstraints(
                    $this->className,
                    $this->propName,
                    $this->propConstraint
                );
            }
        };
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant->registerConstraints($constraints);
        $this->assertSame([$propConstraint], $constraints->getPropertyConstraints($className, $propName));
    }

    public function testRegisteringConstraintsWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IValidationConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IValidationConstraint $propConstraint;

            public function __construct(string $className, string $propName, IValidationConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ConstraintRegistry $constraints): void
            {
                $constraints->registerPropertyConstraints(
                    $this->className,
                    $this->propName,
                    $this->propConstraint
                );
            }
        };
        $constraintRegistryCache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (ConstraintRegistry $constraints) use ($className, $propName, $propConstraint) {
                $actualConstraints = $constraints->getPropertyConstraints($className, $propName);

                return count($actualConstraints) === 1 && $actualConstraints[0] === $propConstraint;
            }));
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant->registerConstraints($constraints);
    }

    public function testRegisteringConstraintsWithNoRegistrantsWillReturnEmptyRegistry(): void
    {
        $constraintRegistryCache = $this->createMock(IConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cachedRegistrant = new CachedConstraintRegistrant($constraintRegistryCache);
        $constraints = new ConstraintRegistry();
        $cachedRegistrant->registerConstraints($constraints);
        $this->assertCount(0, $constraints->getAllMethodConstraints('foo'));
        $this->assertCount(0, $constraints->getAllPropertyConstraints('foo'));
    }
}

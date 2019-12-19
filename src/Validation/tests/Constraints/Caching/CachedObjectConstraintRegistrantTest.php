<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints\Caching;

use Aphiria\Validation\Constraints\Caching\CachedObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintRegistryCache;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\IObjectConstraintRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use Aphiria\Validation\Constraints\ObjectConstraints;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the cached constraint registrant
 */
class CachedObjectConstraintRegistrantTest extends TestCase
{
    public function testRegisteringConstraintsWillIncludeConstraintsInInitialRegistrant(): void
    {
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IConstraint::class);
        $constraintRegistryCache = $this->createMock(IObjectConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $initialConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IObjectConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IConstraint $propConstraint;

            public function __construct(string $className, string $propName, IConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
            {
                $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                    $this->className,
                    [$this->propName => [$this->propConstraint]],
                    []
                ));
            }
        };
        $cachedRegistrant = new CachedObjectConstraintRegistrant($constraintRegistryCache, $initialConstraintRegistrant);
        $objectConstraints = new ObjectConstraintRegistry();
        $cachedRegistrant->registerConstraints($objectConstraints);
        $this->assertSame(
            [$propConstraint],
            $objectConstraints->getConstraintsForClass($className)->getPropertyConstraints($propName)
        );
    }

    public function testRegisteringConstraintsWillIncludeConstraintsInAddedRegistrant(): void
    {
        $constraintRegistryCache = $this->createMock(IObjectConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IObjectConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IConstraint $propConstraint;

            public function __construct(string $className, string $propName, IConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
            {
                $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                    $this->className,
                    [$this->propName => [$this->propConstraint]],
                    []
                ));
            }
        };
        $cachedRegistrant = new CachedObjectConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $objectConstraints = new ObjectConstraintRegistry();
        $cachedRegistrant->registerConstraints($objectConstraints);
        $this->assertSame(
            [$propConstraint],
            $objectConstraints->getConstraintsForClass($className)->getPropertyConstraints($propName)
        );
    }

    public function testRegisteringConstraintsWithCacheThatHitsReturnsThoseConstraints(): void
    {
        /** @var IObjectConstraintRegistryCache|MockObject $constraintRegistryCache */
        $expectedObjectConstraints = new ObjectConstraintRegistry();
        $expectedObjectConstraints->registerObjectConstraints(new ObjectConstraints(
            'foo',
            ['prop' => $this->createMock(IConstraint::class)],
            []
        ));
        $constraintRegistryCache = $this->createMock(IObjectConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedObjectConstraints);
        $objectConstraints = new ObjectConstraintRegistry();
        $cachedRegistrant = new CachedObjectConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->registerConstraints($objectConstraints);
        $this->assertCount(1, $objectConstraints->getConstraintsForClass('foo')->getPropertyConstraints('prop'));
    }

    public function testRegisteringConstraintsWithCacheThatMissesStillRunsTheRegistrants(): void
    {
        $constraintRegistryCache = $this->createMock(IObjectConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IObjectConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IConstraint $propConstraint;

            public function __construct(string $className, string $propName, IConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
            {
                $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                    $this->className,
                    [$this->propName => [$this->propConstraint]],
                    []
                ));
            }
        };
        $cachedRegistrant = new CachedObjectConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $objectConstraints = new ObjectConstraintRegistry();
        $cachedRegistrant->registerConstraints($objectConstraints);
        $this->assertSame(
            [$propConstraint],
            $objectConstraints->getConstraintsForClass($className)->getPropertyConstraints($propName)
        );
    }

    public function testRegisteringConstraintsWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        $constraintRegistryCache = $this->createMock(IObjectConstraintRegistryCache::class);
        $constraintRegistryCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $className = 'foo';
        $propName = 'prop';
        $propConstraint = $this->createMock(IConstraint::class);
        $addedConstraintRegistrant = new class ($className, $propName, $propConstraint) implements IObjectConstraintRegistrant
        {
            private string $className;
            private string $propName;
            private IConstraint $propConstraint;

            public function __construct(string $className, string $propName, IConstraint $propConstraint)
            {
                $this->className = $className;
                $this->propName = $propName;
                $this->propConstraint = $propConstraint;
            }

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintRegistry $objectConstraints): void
            {
                $objectConstraints->registerObjectConstraints(new ObjectConstraints(
                    $this->className,
                    [$this->propName => [$this->propConstraint]],
                    []
                ));
            }
        };
        $constraintRegistryCache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (ObjectConstraintRegistry $objectConstraints) use ($className, $propName, $propConstraint) {
                $actualConstraints = $objectConstraints->getConstraintsForClass($className)->getPropertyConstraints($propName);

                return count($actualConstraints) === 1 && $actualConstraints[0] === $propConstraint;
            }));
        $cachedRegistrant = new CachedObjectConstraintRegistrant($constraintRegistryCache);
        $cachedRegistrant->addConstraintRegistrant($addedConstraintRegistrant);
        $objectConstraints = new ObjectConstraintRegistry();
        $cachedRegistrant->registerConstraints($objectConstraints);
    }
}

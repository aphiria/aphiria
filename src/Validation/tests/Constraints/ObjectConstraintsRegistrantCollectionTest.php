<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\IObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraints;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use PHPUnit\Framework\TestCase;

class ObjectConstraintsRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $constraintsRegistrants = new ObjectConstraintsRegistrantCollection();
        $singleRegistrant = new class() implements IObjectConstraintsRegistrant {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerConstraints(ObjectConstraintsRegistry $objectConstraints): void
            {
                $this->wasInvoked = true;
            }
        };
        $constraintsRegistrants->add($singleRegistrant);
        $objectConstraints = new ObjectConstraintsRegistry();
        $constraintsRegistrants->registerConstraints($objectConstraints);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }

    public function testCacheHitCopiesCachedConstraintsIntoParameterConstraints(): void
    {
        $cachedConstraints = new ObjectConstraintsRegistry();
        $cachedConstraints->registerObjectConstraints(new ObjectConstraints(self::class));
        $cache = $this->createMock(IObjectConstraintsRegistryCache::class);
        $cache->method('get')
            ->willReturn($cachedConstraints);
        $collection = new ObjectConstraintsRegistrantCollection($cache);
        $paramConstraints = new ObjectConstraintsRegistry();
        $collection->registerConstraints($paramConstraints);
        $this->assertEquals($cachedConstraints, $paramConstraints);
    }

    public function testCacheMissPopulatesCache(): void
    {
        $expectedObjectConstraints = new ObjectConstraintsRegistry();
        $cache = $this->createMock(IObjectConstraintsRegistryCache::class);
        $cache->method('get')
            ->willReturn(null);
        $cache->method('set')
            ->with($expectedObjectConstraints);
        $collection = new ObjectConstraintsRegistrantCollection($cache);
        $collection->registerConstraints($expectedObjectConstraints);
        // Dummy assertion
        $this->assertTrue(true);
    }
}

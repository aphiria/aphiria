<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests;

use Aphiria\Application\BootstrapperCollection;
use Aphiria\Application\IBootstrapper;
use PHPUnit\Framework\TestCase;

class BootstrapperCollectionTest extends TestCase
{
    private BootstrapperCollection $bootstrappers;

    protected function setUp(): void
    {
        $this->bootstrappers = new BootstrapperCollection();
    }

    public function testAddManyAddsBootstrappersToCollection(): void
    {
        $bootstrapper1 = $this->createMock(IBootstrapper::class);
        $bootstrapper2 = $this->createMock(IBootstrapper::class);
        $bootstrapper1->expects($this->once())
            ->method('bootstrap');
        $bootstrapper2->expects($this->once())
            ->method('bootstrap');
        $this->bootstrappers->addMany([$bootstrapper1, $bootstrapper2]);
        $this->bootstrappers->bootstrapAll();
    }

    public function testBootstrapBootstrapsAllAddedBootstrappers(): void
    {
        $bootstrapper1 = $this->createMock(IBootstrapper::class);
        $bootstrapper2 = $this->createMock(IBootstrapper::class);
        $bootstrapper1->expects($this->once())
            ->method('bootstrap');
        $bootstrapper2->expects($this->once())
            ->method('bootstrap');
        $this->bootstrappers->add($bootstrapper1);
        $this->bootstrappers->add($bootstrapper2);
        $this->bootstrappers->bootstrapAll();
    }
}

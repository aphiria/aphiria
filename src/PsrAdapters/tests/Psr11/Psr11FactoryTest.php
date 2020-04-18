<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Tests\Psr11;

use Aphiria\DependencyInjection\IContainer;
use Aphiria\PsrAdapters\Psr11\Psr11Container;
use Aphiria\PsrAdapters\Psr11\Psr11Factory;
use PHPUnit\Framework\TestCase;

class Psr11FactoryTest extends TestCase
{
    private Psr11Factory $containerFactory;

    protected function setUp(): void
    {
        $this->containerFactory = new Psr11Factory();
    }

    public function testCreatePsr11ContainerCreatesAphiriaWrapper(): void
    {
        $aphiriaContainer = $this->createMock(IContainer::class);
        $this->assertInstanceOf(Psr11Container::class, $this->containerFactory->createPsr11Container($aphiriaContainer));
    }
}

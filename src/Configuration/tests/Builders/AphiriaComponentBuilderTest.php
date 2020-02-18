<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests\Builders;

use Aphiria\Configuration\Builders\AphiriaComponentBuilder;
use Aphiria\Configuration\Builders\IApplicationBuilder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Aphiria component builder
 */
class AphiriaComponentBuilderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private AphiriaComponentBuilder $componentBuilder;
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
        $this->container = $this->createMock(IContainer::class);
        $this->componentBuilder = new AphiriaComponentBuilder($this->container);
    }

    // TODO: Needs tests
}

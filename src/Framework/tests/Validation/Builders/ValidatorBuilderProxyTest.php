<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilderProxy;
use Aphiria\Validation\Builders\ObjectConstraintsRegistryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validator builder proxy
 */
class ValidatorBuilderProxyTest extends TestCase
{
    private ValidatorBuilderProxy $validatorBuilderProxy;
    /** @var ValidatorBuilder|MockObject */
    private ValidatorBuilder $validatorBuilder;

    protected function setUp(): void
    {
        $this->validatorBuilder = $this->createMock(ValidatorBuilder::class);
        $this->validatorBuilderProxy = new ValidatorBuilderProxy(
            fn () => $this->validatorBuilder
        );
    }

    public function testBuildRegistersObjectConstraintsToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedCallback = fn (ObjectConstraintsRegistryBuilder $objectConstraintsBuilder) => $objectConstraintsBuilder->class('foo');
        $this->validatorBuilder->expects($this->at(0))
            ->method('withObjectConstraints')
            ->with($expectedCallback);
        $this->validatorBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->validatorBuilderProxy->withObjectConstraints($expectedCallback);
        $this->validatorBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildWithAnnotationsConfiguresProxiedComponentBuilderToUseAnnotations(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $this->validatorBuilder->expects($this->at(0))
            ->method('withAnnotations');
        $this->validatorBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->validatorBuilderProxy->withAnnotations();
        $this->validatorBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(ValidatorBuilder::class, $this->validatorBuilderProxy->getProxiedType());
    }
}

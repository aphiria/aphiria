<?php

namespace Aphiria\Framework\Tests\ExtensionMethods\Components;

use Aphiria\ExtensionMethods\ExtensionMethodRegistry;
use Aphiria\Framework\ExtensionMethods\Components\ExtensionMethodComponent;
use PHPUnit\Framework\TestCase;

class ExtensionMethodComponentTest extends TestCase
{
    private ExtensionMethodComponent $extensionMethodComponent;

    protected function setUp(): void
    {
        ExtensionMethodRegistry::reset();
        $this->extensionMethodComponent = new ExtensionMethodComponent();
    }

    public function testWithExtensionMethodRegistersExtensionMethodImmediately(): void
    {
        $foo = new class() {
        };
        $expectedExtensionMethod = fn (): string => 'foo';
        $this->extensionMethodComponent->withExtensionMethod($foo::class, 'foo', $expectedExtensionMethod);
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
        // Building shouldn't affect anything
        $this->extensionMethodComponent->build();
        $this->assertSame($expectedExtensionMethod, ExtensionMethodRegistry::getExtensionMethod($foo, 'foo'));
    }
}

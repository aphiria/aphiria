<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\Context;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\CallException;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\DependencyInjection\Tests\Mocks\Bar;
use Aphiria\DependencyInjection\Tests\Mocks\BaseClass;
use Aphiria\DependencyInjection\Tests\Mocks\Blah;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithConcreteClass;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithDefaultValueObject;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithDefaultValuePrimitives;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithInterface;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithMixOfConcreteClassesAndPrimitives;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithMixOfInterfacesAndPrimitives;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithNullableObject;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithPrimitives;
use Aphiria\DependencyInjection\Tests\Mocks\ConstructorWithSetters;
use Aphiria\DependencyInjection\Tests\Mocks\Dave;
use Aphiria\DependencyInjection\Tests\Mocks\Foo;
use Aphiria\DependencyInjection\Tests\Mocks\IFoo;
use Aphiria\DependencyInjection\Tests\Mocks\IPerson;
use Aphiria\DependencyInjection\Tests\Mocks\MagicCallMethod;
use Aphiria\DependencyInjection\Tests\Mocks\StaticSetters;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dependency injection container
 */
class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        // Make sure the global instance gets wiped before each test
        Container::$globalInstance = null;
    }

    public function testBindingTargetedFactory(): void
    {
        $this->container->for(
            new TargetedContext(ConstructorWithInterface::class),
            fn (IContainer $container) => $container->bindFactory(IFoo::class, fn () => new Bar)
        );
        $instance1 = $this->container->resolve(ConstructorWithInterface::class);
        $instance2 = $this->container->resolve(ConstructorWithInterface::class);
        $this->assertInstanceOf(Bar::class, $instance1->getFoo());
        $this->assertInstanceOf(Bar::class, $instance2->getFoo());
        $this->assertNotSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingTargetedSingletonFactory(): void
    {
        $this->container->for(
            new TargetedContext(ConstructorWithInterface::class),
            fn (IContainer $container) => $container->bindFactory(IFoo::class, fn () => new Bar, true)
        );
        $instance1 = $this->container->resolve(ConstructorWithInterface::class);
        $instance2 = $this->container->resolve(ConstructorWithInterface::class);
        $this->assertInstanceOf(ConstructorWithInterface::class, $instance1);
        $this->assertInstanceOf(Bar::class, $instance1->getFoo());
        $this->assertSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingToAbstractClass(): void
    {
        $prototypeContainer = new Container();
        $prototypeContainer->bindClass(BaseClass::class, Bar::class);
        $prototypeInstance = $prototypeContainer->resolve(BaseClass::class);
        $this->assertInstanceOf(Bar::class, $prototypeInstance);
        $this->assertNotSame($prototypeInstance, $prototypeContainer->resolve(BaseClass::class));
        $singletonContainer = new Container();
        $singletonContainer->bindClass(BaseClass::class, Bar::class, [], true);
        $singletonInstance = $singletonContainer->resolve(BaseClass::class);
        $this->assertInstanceOf(Bar::class, $singletonInstance);
        $this->assertSame($singletonInstance, $singletonContainer->resolve(BaseClass::class));
    }

    public function testBindingUniversalFactory(): void
    {
        $this->container->bindFactory(IFoo::class, fn () => new Bar);
        $instance1 = $this->container->resolve(IFoo::class);
        $instance2 = $this->container->resolve(IFoo::class);
        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingUniversalSingletonFactory(): void
    {
        $this->container->bindFactory(IFoo::class, fn () => new Bar, true);
        $instance1 = $this->container->resolve(ConstructorWithInterface::class);
        $instance2 = $this->container->resolve(ConstructorWithInterface::class);
        $this->assertInstanceOf(ConstructorWithInterface::class, $instance1);
        $this->assertInstanceOf(Bar::class, $instance1->getFoo());
        $this->assertInstanceOf(Bar::class, $instance2->getFoo());
        $this->assertSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1, $instance2);
    }

    public function testCallingMethodWithPrimitiveTypes(): void
    {
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setPrimitive', ['foo']);
        $this->assertSame('foo', $instance->getPrimitive());
        $result = $this->container->callClosure(fn ($primitive) => $primitive, ['foo']);
        $this->assertEquals('foo', $result);
    }

    public function testCallingMethodWithPrimitiveTypesWithoutSpecifyingValue(): void
    {
        $this->expectException(CallException::class);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setPrimitive');
    }

    public function testCallingMethodWithTypeHintedAndPrimitiveTypes(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class, [], true);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setBoth', ['foo']);
        $this->assertInstanceOf(Bar::class, $instance->getInterface());
        $this->assertSame('foo', $instance->getPrimitive());
        $response = $this->container->callClosure(
            fn (IFoo $interface, $primitive) => \get_class($interface) . ':' . $primitive,
            ['foo']
        );
        $this->assertEquals(Bar::class . ':foo', $response);
    }

    public function testCallingMethodWithTypeHints(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class, [], true);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setInterface');
        $this->assertInstanceOf(Bar::class, $instance->getInterface());
        $response = $this->container->callClosure(fn (IFoo $interface) => \get_class($interface));
        $this->assertEquals(Bar::class, $response);
    }

    public function testCallingNonExistentMethod(): void
    {
        $this->expectException(CallException::class);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'foobar');
    }

    public function testCallingNonExistentMethodAndIgnoringThatItIsMissing(): void
    {
        $instance = new ConstructorWithSetters();
        $this->assertNull($this->container->callMethod($instance, 'foobar', [], true));
    }

    public function testCallingNonExistentMethodOnClassThatHasMagicCallMethod(): void
    {
        $instance = new MagicCallMethod();
        $this->assertNull($this->container->callMethod($instance, 'foobar', [], true));
    }

    public function testCallingStaticMethod(): void
    {
        $person = new Dave;
        $this->container->bindInstance(IPerson::class, $person);
        $this->container->callMethod(StaticSetters::class, 'setStaticSetterDependency', [$person]);
        $this->assertSame($person, StaticSetters::$staticDependency);
    }

    public function testCheckingIfTargetBoundInterfaceIsBound(): void
    {
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindClass(IFoo::class, Bar::class);
        });
        $this->assertTrue($this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->hasBinding(IFoo::class);
        }));
        // Reset for factory
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->unbind(IFoo::class);
        });
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindFactory(IFoo::class, function () {
                return new Bar;
            });
        });
        $this->assertTrue($this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->hasBinding(IFoo::class);
        }));
        // Reset for instance
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->unbind(IFoo::class);
        });
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindInstance(IFoo::class, new Bar);
        });
        $this->assertTrue($this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->hasBinding(IFoo::class);
        }));
    }

    public function testCheckingIfUniversallyBoundInterfaceIsBound(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class);
        $this->assertTrue($this->container->hasBinding(IFoo::class));
        $this->container->unbind(IFoo::class);
        $this->container->bindFactory(IFoo::class, function () {
            return new Bar;
        });
        $this->assertTrue($this->container->hasBinding(IFoo::class));
    }

    public function testCheckingTargetHasBindingWhenItOnlyHasUniversalBinding(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class);
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $this->assertTrue($container->hasBinding(IFoo::class));
        });
    }

    public function testCheckingUnboundTargetedBinding(): void
    {
        $this->assertFalse(
            $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
                return $container->hasBinding(IFoo::class);
            })
        );
    }

    public function testCheckingUnboundUniversalBinding(): void
    {
        $this->assertFalse($this->container->hasBinding(IFoo::class));
    }

    public function testCheckingUniversalBinding(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class);
        $this->assertTrue($this->container->hasBinding(IFoo::class));
    }

    public function testCreatingInstanceWithUnsetConstructorPrimitive(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve(ConstructorWithPrimitives::class);
    }

    public function testCreatingInterfaceWithoutBinding(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve(IFoo::class);
    }

    public function testCreatingPrototypeInstanceWithConcreteDependency(): void
    {
        $newInstance = $this->container->resolve(ConstructorWithConcreteClass::class);
        $this->assertInstanceOf(ConstructorWithConcreteClass::class, $newInstance);
    }

    public function testCreatingPrototypeObjectWithConstructorPrimitive(): void
    {
        $this->container->bindClass(ConstructorWithPrimitives::class, ConstructorWithPrimitives::class, ['foo', 'bar']);
        $instance = $this->container->resolve(ConstructorWithPrimitives::class);
        $this->assertInstanceOf(ConstructorWithPrimitives::class, $instance);
        $this->assertNotSame(
            $instance,
            $this->container->resolve(ConstructorWithPrimitives::class)
        );
    }

    public function testCreatingPrototypeObjectWithUnsetConstructorPrimitive(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve(ConstructorWithPrimitives::class);
    }

    public function testCreatingPrototypeObjectWithUnsetConstructorPrimitiveWithDefaultValue(): void
    {
        $this->container->bindClass(
            ConstructorWithDefaultValuePrimitives::class,
            ConstructorWithDefaultValuePrimitives::class,
            ['foo']
        );
        $instance = $this->container->resolve(ConstructorWithDefaultValuePrimitives::class);
        $this->assertInstanceOf(ConstructorWithDefaultValuePrimitives::class, $instance);
        $this->assertNotSame(
            $instance,
            $this->container->resolve(ConstructorWithDefaultValuePrimitives::class)
        );
    }

    public function testCreatingSingletonInstanceWithConcreteDependency(): void
    {
        $sharedInstance = $this->container->resolve(ConstructorWithConcreteClass::class);
        $this->assertInstanceOf(ConstructorWithConcreteClass::class, $sharedInstance);
    }

    public function testCreatingSingletonInstanceWithConstructorPrimitive(): void
    {
        $this->container->bindClass(ConstructorWithPrimitives::class, ConstructorWithPrimitives::class, ['foo', 'bar'], true);
        $instance = $this->container->resolve(ConstructorWithPrimitives::class);
        $this->assertInstanceOf(ConstructorWithPrimitives::class, $instance);
        $this->assertSame(
            $instance,
            $this->container->resolve(ConstructorWithPrimitives::class)
        );
    }

    public function testCreatingSingletonInstanceWithUnsetConstructorPrimitiveWithDefaultValue(): void
    {
        $this->container->bindClass(
            ConstructorWithDefaultValuePrimitives::class,
            ConstructorWithDefaultValuePrimitives::class,
            ['foo'],
            true
        );
        $instance = $this->container->resolve(ConstructorWithDefaultValuePrimitives::class);
        $this->assertInstanceOf(ConstructorWithDefaultValuePrimitives::class, $instance);
        $this->assertSame(
            $instance,
            $this->container->resolve(ConstructorWithDefaultValuePrimitives::class)
        );
    }

    public function testDependencyThatHasDependency(): void
    {
        $tests = function () {
            $this->assertInstanceOf(
                Foo::class,
                $this->container->resolve(IFoo::class)
            );
        };
        $this->container->bindClass(IFoo::class, Foo::class);
        $this->container->bindClass(IPerson::class, Dave::class);
        $tests();
        $this->container->unbind([IFoo::class, IPerson::class]);
        $this->container->bindFactory(IFoo::class, function () {
            return new Foo(new Dave);
        });
        $this->container->bindFactory(IPerson::class, function () {
            return new Dave;
        });
        $tests();
    }

    public function testDependencyThatHasDependencyWithoutBindingAllDependencies(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->bindClass(IFoo::class, Foo::class);
        $this->container->resolve(IFoo::class);
    }

    public function testFactoryDependenciesInPrototypeAreNotSame(): void
    {
        $this->container->bindClass(
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            [23]
        );
        $this->container->bindFactory(IFoo::class, function () {
            return new Bar;
        });
        $this->container->bindFactory(IPerson::class, function () {
            return new Dave;
        });
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        $this->assertNotSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1->getPerson(), $instance2->getPerson());
    }

    public function testFactoryDependenciesInSingleton(): void
    {
        $this->container->bindClass(
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            [23],
            true
        );
        $this->container->bindFactory(IFoo::class, function () {
            return new Bar;
        });
        $this->container->bindFactory(IPerson::class, function () {
            return new Dave;
        });
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        $this->assertInstanceOf(ConstructorWithMixOfInterfacesAndPrimitives::class, $instance1);
        $this->assertSame($instance1, $instance2);
        $this->assertEquals(23, $instance1->getId());
        $this->assertEquals(23, $instance2->getId());
    }

    public function testForWithInvalidParameterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Context must be an instance of ' . Context::class . ' or string');
        $this->container->for(1, fn (IContainer $container) => null);
    }

    public function testForWithStringContextCreatesTargetedBinding(): void
    {
        $this->container->for('foo', fn (IContainer $container) => $container->bindInstance(IFoo::class, new Bar));
        $this->container->for('foo', function (IContainer $container) {
            $this->assertInstanceOf(Bar::class, $container->resolve(IFoo::class));
        });
    }

    public function testGettingTargetedBindingWhenOneDoesNotExistButUniversalBindingExists(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class);
        $instance = $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $this->assertInstanceOf(IFoo::class, $instance);
    }

    public function testInstancesAreDifferentWhenUsingFactory(): void
    {
        $this->container->bindFactory(BaseClass::class, function () {
            return new Bar;
        });
        $instance1 = $this->container->resolve(BaseClass::class);
        $instance2 = $this->container->resolve(BaseClass::class);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMixOfConcreteClassesAndPrimitivesInConstructorForPrototype(): void
    {
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $sharedInstance */
        $this->container->bindClass(
            ConstructorWithMixOfConcreteClassesAndPrimitives::class,
            ConstructorWithMixOfConcreteClassesAndPrimitives::class,
            [23]
        );
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $instance */
        $instance = $this->container->resolve(ConstructorWithMixOfConcreteClassesAndPrimitives::class);
        $this->assertInstanceOf(ConstructorWithMixOfConcreteClassesAndPrimitives::class, $instance);
        $this->assertEquals(23, $instance->getId());
        $this->assertNotSame($instance, $this->container->resolve(ConstructorWithMixOfConcreteClassesAndPrimitives::class));
    }

    public function testMixOfConcreteClassesAndPrimitivesInConstructorForSingleton(): void
    {
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $instance */
        $this->container->bindClass(
            ConstructorWithMixOfConcreteClassesAndPrimitives::class,
            ConstructorWithMixOfConcreteClassesAndPrimitives::class,
            [23],
            true
        );
        $instance = $this->container->resolve(ConstructorWithMixOfConcreteClassesAndPrimitives::class);
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $newInstance */
        $this->assertInstanceOf(ConstructorWithMixOfConcreteClassesAndPrimitives::class, $instance);
        $this->assertEquals(23, $instance->getId());
        $this->assertSame($instance, $this->container->resolve(ConstructorWithMixOfConcreteClassesAndPrimitives::class));
    }

    public function testMultipleTargetedBindings(): void
    {
        $this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            $container->bindClass(['foo', 'bar'], Bar::class);
        });
        $this->assertTrue($this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            return $container->hasBinding('foo');
        }));
        $this->assertTrue($this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            return $container->hasBinding('bar');
        }));
    }

    public function testMultipleUniversalBindings(): void
    {
        $this->container->bindClass(['foo', 'bar'], Bar::class);
        $this->assertTrue($this->container->hasBinding('foo'));
        $this->assertTrue($this->container->hasBinding('bar'));
    }

    public function testResolvingClassWithNullableObjectInConstructorThatCannotBeResolvedUsesNull(): void
    {
        $instance = $this->container->resolve(ConstructorWithNullableObject::class);
        $this->assertInstanceOf(ConstructorWithNullableObject::class, $instance);
        $this->assertInstanceOf(DateTime::class, $instance->getFoo());
    }

    public function testResolvingClassWithObjectInConstructorThatCannotBeResolvedUsesDefaultValueIfAvailable(): void
    {
        $instance = $this->container->resolve(ConstructorWithDefaultValueObject::class);
        $this->assertInstanceOf(ConstructorWithDefaultValueObject::class, $instance);
        $this->assertInstanceOf(DateTime::class, $instance->getFoo());
    }

    public function testResolvingInstanceBoundInTargetedCallback(): void
    {
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindFactory(IFoo::class, function () {
                return new Bar;
            });
        });
        $instance = $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $this->assertInstanceOf(Bar::class, $instance);
    }

    public function testResolvingPrototypeForTarget(): void
    {
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindClass(IFoo::class, Bar::class);
        });
        $instance1 = $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $instance2 = $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertInstanceOf(Bar::class, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testResolvingPrototypeNonExistentClass(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve('DoesNotExist');
    }

    public function testResolvingSingletonForTarget(): void
    {
        $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            $container->bindClass(IFoo::class, Bar::class, [], true);
        });
        $instance1 = $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $instance2 = $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->resolve(IFoo::class);
        });
        $this->assertInstanceOf(Bar::class, $instance1);
        $this->assertInstanceOf(Bar::class, $instance2);
        $this->assertSame($instance1, $instance2);

        // Make sure that the singleton is not bound universally
        try {
            $this->container->resolve(ConstructorWithInterface::class);
            // The line above should throw an exception, so fail if we've gotten here
            $this->fail('Targeted singleton accidentally bound universally');
        } catch (ResolutionException $ex) {
            // Don't do anything
        }
    }

    public function testSingletonDependenciesInPrototype(): void
    {
        $this->container->bindClass(
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            [23]
        );
        $this->container->bindClass(IFoo::class, Bar::class, [], true);
        $this->container->bindClass(IPerson::class, Dave::class, [], true);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        $this->assertSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertSame($instance1->getPerson(), $instance2->getPerson());
        $this->assertNotSame($instance1, $instance2);
    }

    public function testSingletonDependenciesInSingleton(): void
    {
        $this->container->bindClass(
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            ConstructorWithMixOfInterfacesAndPrimitives::class,
            [23],
            true
        );
        $this->container->bindClass(IFoo::class, Bar::class, [], true);
        $this->container->bindClass(IPerson::class, Dave::class, [], true);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve(ConstructorWithMixOfInterfacesAndPrimitives::class);
        $this->assertInstanceOf(ConstructorWithMixOfInterfacesAndPrimitives::class, $instance1);
        $this->assertSame($instance1, $instance2);
        $this->assertEquals(23, $instance1->getId());
        $this->assertEquals(23, $instance2->getId());
        $this->assertSame($instance1->getPerson(), $instance2->getPerson());
    }

    public function testTargetedBindingOfInstanceToInterface(): void
    {
        $targetedInstance = new Bar();
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) use ($targetedInstance) {
            $container->bindInstance(IFoo::class, $targetedInstance);
        });
        // This universal binding should NOT take precedence over the class binding
        $this->container->bindClass(IFoo::class, Blah::class);
        $resolvedInstance = $this->container->resolve(ConstructorWithInterface::class);
        $this->assertSame($targetedInstance, $resolvedInstance->getFoo());
    }

    public function testTargetedFactoryBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            $container->bindFactory(IFoo::class, function () {
                return new Bar();
            });
        });
        $this->container->for(new TargetedContext('bar'), function (IContainer $container) {
            $container->bindFactory('doesNotExist', function () {
                return new Bar();
            });
        });
        $this->assertFalse($this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for(new TargetedContext('bar'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedInstanceBindingsOnlyApplyToNextCall(): void
    {
        $instance1 = new Bar();
        $instance2 = new Bar();
        $this->container->for(new TargetedContext('foo'), function (IContainer $container) use ($instance1) {
            $container->bindInstance(IFoo::class, $instance1);
        });
        $this->container->for(new TargetedContext('bar'), function (IContainer $container) use ($instance2) {
            $container->bindInstance('doesNotExist', $instance2);
        });
        $this->assertFalse($this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for(new TargetedContext('bar'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedPrototypeBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            $container->bindClass(IFoo::class, 'bar');
        });
        $this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            $container->bindClass('doesNotExist', 'bar');
        });
        $this->assertFalse($this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedSingletonBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            $container->bindClass(IFoo::class, 'bar', [], true);
        });
        $this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            $container->bindClass('doesNotExist', 'bar', [], true);
        });
        $this->assertFalse($this->container->for(new TargetedContext('foo'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for(new TargetedContext('baz'), function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTryResolvingReturnsFalseOnFailure(): void
    {
        $instance = null;
        $this->assertFalse($this->container->tryResolve(IFoo::class, $instance));
        $this->assertNull($instance);
    }

    public function testTryResolvingReturnsTrueAndSetsInstanceOnSuccess(): void
    {
        $expectedInstance = new Bar();
        $this->container->bindInstance(IFoo::class, $expectedInstance);
        $instance = null;
        $this->assertTrue($this->container->tryResolve(IFoo::class, $instance));
        $this->assertSame($expectedInstance, $instance);
    }

    public function testTryResolvingSetsInstanceToNullOnFailure(): void
    {
        $instance = $this;
        $this->assertFalse($this->container->tryResolve(IFoo::class, $instance));
        $this->assertNull($instance);
    }

    public function testUnbindingFactory(): void
    {
        $this->container->bindFactory(BaseClass::class, function () {
            return new Bar;
        });
        $this->container->unbind(BaseClass::class);
        $this->assertFalse($this->container->hasBinding(BaseClass::class));
    }

    public function testUnbindingMultipleInterfaces(): void
    {
        $this->container->bindClass('foo', 'bar');
        $this->container->bindClass('baz', 'blah');
        $this->container->unbind(['foo', 'baz']);
        $this->assertFalse($this->container->hasBinding('foo'));
        $this->assertFalse($this->container->hasBinding('baz'));
    }

    public function testUnbindingTargetedBinding(): void
    {
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->bindClass(IFoo::class, Bar::class);
        });
        $this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            $container->unbind(IFoo::class);
        });
        $this->assertFalse($this->container->for(new TargetedContext(ConstructorWithInterface::class), function (IContainer $container) {
            return $container->hasBinding(IFoo::class);
        }));
    }

    public function testUnbindingUniversalBinding(): void
    {
        $this->container->bindClass(IFoo::class, Bar::class);
        $this->container->unbind(IFoo::class);
        $this->assertFalse($this->container->hasBinding(IFoo::class));
    }

    public function testUniversallyBindingInstanceToInterface(): void
    {
        $instance = new Bar();
        $this->container->bindInstance(IFoo::class, $instance);
        $this->assertSame($instance, $this->container->resolve(IFoo::class));
        $this->assertNotSame($instance, $this->container->resolve(Bar::class));
    }

    public function testWakingUpContainerWhenGlobalInstanceIsSetUsesGlobalInstancesBindings(): void
    {
        $container = new Container();
        Container::$globalInstance = $container;
        $expectedFoo = new Bar();
        $container->bindInstance(IFoo::class, $expectedFoo);
        $this->assertTrue($container->hasBinding(IFoo::class));
        /** @var Container $deserializedContainer */
        $deserializedContainer = unserialize(serialize($container));
        $this->assertTrue($deserializedContainer->hasBinding(IFoo::class));
        $this->assertSame($expectedFoo, $deserializedContainer->resolve(IFoo::class));
    }

    public function testWakingUpContainerWhenNoGlobalInstanceIsSetResetsTheBindings(): void
    {
        $container = new Container();
        $container->bindInstance(IFoo::class, new Bar());
        $this->assertTrue($container->hasBinding(IFoo::class));
        /** @var Container $deserializedContainer */
        $deserializedContainer = unserialize(serialize($container));
        $this->assertFalse($deserializedContainer->hasBinding(IFoo::class));
    }
}

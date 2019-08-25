<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/dependency-injection/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Tests;

use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\DependencyInjectionException;
use Aphiria\DependencyInjection\ResolutionException;
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
use PHPUnit\Framework\TestCase;

/**
 * Tests the dependency injection container
 */
class ContainerTest extends TestCase
{
    private Container $container;
    /** @var string The name of the simple interface to use in tests */
    private string $fooInterface = IFoo::class;
    /** @var string The name of the simple interface to use in tests */
    private string $personInterface = IPerson::class;
    /** @var string The name of a class that implements IPerson */
    private string $concretePerson = Dave::class;
    /** @var string The name of the base class to use in tests */
    private string $baseClass = BaseClass::class;
    /** @var string The name of the class that implements IFoo to use in tests */
    private string $concreteFoo = Bar::class;
    /** @var string The name of a second class that implements the IFoo to use in tests */
    private string $secondConcreteIFoo = Blah::class;
    /** @var string The name of a another class that implements the IFoo to use in tests */
    private string $concreteFooWithIPersonDependency = Foo::class;
    /** @var string The name of the class that accepts the IFoo in its constructor */
    private string $constructorWithIFoo = ConstructorWithInterface::class;
    /** @var string The name of the class that accepts the concrete class in its constructor */
    private string $constructorWithConcreteClass = ConstructorWithConcreteClass::class;
    /** @var string The name of the class that accepts a mix of interfaces and primitives in its constructor */
    private string $constructorWithInterfacesAndPrimitives = ConstructorWithMixOfInterfacesAndPrimitives::class;
    /** @var string The name of the class that accepts a mix of class names and primitives in its constructor */
    private string $constructorWithConcreteClassesAndPrimitives = ConstructorWithMixOfConcreteClassesAndPrimitives::class;
    /** @var string The name of the class that accepts the primitives in its constructor */
    private string $constructorWithPrimitives = ConstructorWithPrimitives::class;
    /** @var string The name of the class that accepts primitives with default values in its constructor */
    private string $constructorWithDefaultValuePrimitives = ConstructorWithDefaultValuePrimitives::class;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testBindingTargetedFactory(): void
    {
        $this->container->for(
            $this->constructorWithIFoo,
            fn (IContainer $container) => $container->bindFactory($this->fooInterface, fn () => new $this->concreteFoo)
        );
        $instance1 = $this->container->resolve($this->constructorWithIFoo);
        $instance2 = $this->container->resolve($this->constructorWithIFoo);
        $this->assertInstanceOf($this->concreteFoo, $instance1->getFoo());
        $this->assertInstanceOf($this->concreteFoo, $instance2->getFoo());
        $this->assertNotSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingTargetedSingletonFactory(): void
    {
        $this->container->for(
            $this->constructorWithIFoo,
            fn (IContainer $container) => $container->bindFactory($this->fooInterface, fn () => new $this->concreteFoo, true)
        );
        $instance1 = $this->container->resolve($this->constructorWithIFoo);
        $instance2 = $this->container->resolve($this->constructorWithIFoo);
        $this->assertInstanceOf($this->constructorWithIFoo, $instance1);
        $this->assertInstanceOf($this->concreteFoo, $instance1->getFoo());
        $this->assertSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingToAbstractClass(): void
    {
        $prototypeContainer = new Container();
        $prototypeContainer->bindPrototype($this->baseClass, $this->concreteFoo);
        $prototypeInstance = $prototypeContainer->resolve($this->baseClass);
        $this->assertInstanceOf($this->concreteFoo, $prototypeInstance);
        $this->assertNotSame($prototypeInstance, $prototypeContainer->resolve($this->baseClass));
        $singletonContainer = new Container();
        $singletonContainer->bindSingleton($this->baseClass, $this->concreteFoo);
        $singletonInstance = $singletonContainer->resolve($this->baseClass);
        $this->assertInstanceOf($this->concreteFoo, $singletonInstance);
        $this->assertSame($singletonInstance, $singletonContainer->resolve($this->baseClass));
    }

    public function testBindingUniversalFactory(): void
    {
        $this->container->bindFactory($this->fooInterface, fn () => new $this->concreteFoo);
        $instance1 = $this->container->resolve($this->fooInterface);
        $instance2 = $this->container->resolve($this->fooInterface);
        $this->assertInstanceOf($this->concreteFoo, $instance1);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testBindingUniversalSingletonFactory(): void
    {
        $this->container->bindFactory($this->fooInterface, fn () => new $this->concreteFoo, true);
        $instance1 = $this->container->resolve($this->constructorWithIFoo);
        $instance2 = $this->container->resolve($this->constructorWithIFoo);
        $this->assertInstanceOf($this->constructorWithIFoo, $instance1);
        $this->assertInstanceOf($this->concreteFoo, $instance1->getFoo());
        $this->assertInstanceOf($this->concreteFoo, $instance2->getFoo());
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
        $this->expectException(DependencyInjectionException::class);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setPrimitive');
    }

    public function testCallingMethodWithTypeHintedAndPrimitiveTypes(): void
    {
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setBoth', ['foo']);
        $this->assertInstanceOf($this->concreteFoo, $instance->getInterface());
        $this->assertSame('foo', $instance->getPrimitive());
        $response = $this->container->callClosure(
            fn (IFoo $interface, $primitive) => \get_class($interface) . ':' . $primitive,
            ['foo']
        );
        $this->assertEquals($this->concreteFoo . ':foo', $response);
    }

    public function testCallingMethodWithTypeHints(): void
    {
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $instance = new ConstructorWithSetters();
        $this->container->callMethod($instance, 'setInterface');
        $this->assertInstanceOf($this->concreteFoo, $instance->getInterface());
        $response = $this->container->callClosure(fn (IFoo $interface) => \get_class($interface));
        $this->assertEquals($this->concreteFoo, $response);
    }

    public function testCallingNonExistentMethod(): void
    {
        $this->expectException(DependencyInjectionException::class);
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
        $person = new $this->concretePerson;
        $this->container->bindInstance($this->personInterface, $person);
        $this->container->callMethod(StaticSetters::class, 'setStaticSetterDependency', [$person]);
        $this->assertSame($person, StaticSetters::$staticDependency);
    }

    public function testCheckingIfTargetBoundInterfaceIsBound(): void
    {
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindPrototype($this->fooInterface, $this->concreteFoo);
        });
        $this->assertTrue($this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->hasBinding($this->fooInterface);
        }));
        // Reset for factory
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->unbind($this->fooInterface);
        });
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindFactory($this->fooInterface, function () {
                return new $this->concreteFoo;
            });
        });
        $this->assertTrue($this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->hasBinding($this->fooInterface);
        }));
        // Reset for instance
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->unbind($this->fooInterface);
        });
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindInstance($this->fooInterface, new $this->concreteFoo);
        });
        $this->assertTrue($this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->hasBinding($this->fooInterface);
        }));
        // Reset for singleton
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->unbind($this->fooInterface);
        });
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindSingleton($this->fooInterface, $this->concreteFoo);
        });
        $this->assertTrue($this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->hasBinding($this->fooInterface);
        }));
    }

    public function testCheckingIfUniversallyBoundInterfaceIsBound(): void
    {
        $this->container->bindPrototype($this->fooInterface, $this->concreteFoo);
        $this->assertTrue($this->container->hasBinding($this->fooInterface));
        $this->container->unbind($this->fooInterface);
        $this->container->bindFactory($this->fooInterface, function () {
            return new $this->concreteFoo;
        });
        $this->assertTrue($this->container->hasBinding($this->fooInterface));
    }

    public function testCheckingTargetHasBindingWhenItOnlyHasUniversalBinding(): void
    {
        $this->container->bindPrototype($this->fooInterface, $this->concreteFoo);
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $this->assertTrue($container->hasBinding($this->fooInterface));
        });
    }

    public function testCheckingUnboundTargetedBinding(): void
    {
        $this->assertFalse(
            $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
                return $container->hasBinding($this->fooInterface);
            })
        );
    }

    public function testCheckingUnboundUniversalBinding(): void
    {
        $this->assertFalse($this->container->hasBinding($this->fooInterface));
    }

    public function testCheckingUniversalBinding(): void
    {
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $this->assertTrue($this->container->hasBinding($this->fooInterface));
    }

    public function testCreatingInstanceWithUnsetConstructorPrimitive(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve($this->constructorWithPrimitives);
    }

    public function testCreatingInterfaceWithoutBinding(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve($this->fooInterface);
    }

    public function testCreatingPrototypeInstanceWithConcreteDependency(): void
    {
        $newInstance = $this->container->resolve($this->constructorWithConcreteClass);
        $this->assertInstanceOf($this->constructorWithConcreteClass, $newInstance);
    }

    public function testCreatingPrototypeObjectWithConstructorPrimitive(): void
    {
        $this->container->bindPrototype($this->constructorWithPrimitives, null, ['foo', 'bar']);
        $instance = $this->container->resolve($this->constructorWithPrimitives);
        $this->assertInstanceOf($this->constructorWithPrimitives, $instance);
        $this->assertNotSame(
            $instance,
            $this->container->resolve($this->constructorWithPrimitives)
        );
    }

    public function testCreatingPrototypeObjectWithUnsetConstructorPrimitive(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve($this->constructorWithPrimitives);
    }

    public function testCreatingPrototypeObjectWithUnsetConstructorPrimitiveWithDefaultValue(): void
    {
        $this->container->bindPrototype($this->constructorWithDefaultValuePrimitives, null, ['foo']);
        $instance = $this->container->resolve($this->constructorWithDefaultValuePrimitives);
        $this->assertInstanceOf($this->constructorWithDefaultValuePrimitives, $instance);
        $this->assertNotSame(
            $instance,
            $this->container->resolve($this->constructorWithDefaultValuePrimitives)
        );
    }

    public function testCreatingSingletonInstanceWithConcreteDependency(): void
    {
        $sharedInstance = $this->container->resolve($this->constructorWithConcreteClass);
        $this->assertInstanceOf($this->constructorWithConcreteClass, $sharedInstance);
    }

    public function testCreatingSingletonInstanceWithConstructorPrimitive(): void
    {
        $this->container->bindSingleton($this->constructorWithPrimitives, null, ['foo', 'bar']);
        $instance = $this->container->resolve($this->constructorWithPrimitives);
        $this->assertInstanceOf($this->constructorWithPrimitives, $instance);
        $this->assertSame(
            $instance,
            $this->container->resolve($this->constructorWithPrimitives)
        );
    }

    public function testCreatingSingletonInstanceWithUnsetConstructorPrimitiveWithDefaultValue(): void
    {
        $this->container->bindSingleton($this->constructorWithDefaultValuePrimitives, null, ['foo']);
        $instance = $this->container->resolve($this->constructorWithDefaultValuePrimitives);
        $this->assertInstanceOf($this->constructorWithDefaultValuePrimitives, $instance);
        $this->assertSame(
            $instance,
            $this->container->resolve($this->constructorWithDefaultValuePrimitives)
        );
    }

    public function testDependencyThatHasDependency(): void
    {
        $tests = function () {
            $this->assertInstanceOf(
                $this->concreteFooWithIPersonDependency,
                $this->container->resolve($this->fooInterface)
            );
        };
        $this->container->bindPrototype($this->fooInterface, $this->concreteFooWithIPersonDependency);
        $this->container->bindPrototype($this->personInterface, $this->concretePerson);
        $tests();
        $this->container->unbind([$this->fooInterface, $this->personInterface]);
        $this->container->bindFactory($this->fooInterface, function () {
            return new $this->concreteFooWithIPersonDependency(new $this->concretePerson);
        });
        $this->container->bindFactory($this->personInterface, function () {
            return new $this->concretePerson;
        });
        $tests();
    }

    public function testDependencyThatHasDependencyWithoutBindingAllDependencies(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->bindSingleton($this->fooInterface, $this->concreteFooWithIPersonDependency);
        $this->container->resolve($this->fooInterface);
    }

    public function testFactoryDependenciesInPrototypeAreNotSame(): void
    {
        $this->container->bindPrototype($this->constructorWithInterfacesAndPrimitives, null, [23]);
        $this->container->bindFactory($this->fooInterface, function () {
            return new $this->concreteFoo;
        });
        $this->container->bindFactory($this->personInterface, function () {
            return new $this->concretePerson;
        });
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        $this->assertNotSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertNotSame($instance1->getPerson(), $instance2->getPerson());
    }

    public function testFactoryDependenciesInSingleton(): void
    {
        $this->container->bindSingleton($this->constructorWithInterfacesAndPrimitives, null, [23]);
        $this->container->bindFactory($this->fooInterface, function () {
            return new $this->concreteFoo;
        });
        $this->container->bindFactory($this->personInterface, function () {
            return new $this->concretePerson;
        });
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        $this->assertInstanceOf($this->constructorWithInterfacesAndPrimitives, $instance1);
        $this->assertSame($instance1, $instance2);
        $this->assertEquals(23, $instance1->getId());
        $this->assertEquals(23, $instance2->getId());
    }

    public function testGettingTargetedBindingWhenOneDoesNotExistButUniversalBindingExists(): void
    {
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $instance = $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $this->assertInstanceOf($this->fooInterface, $instance);
    }

    public function testInstancesAreDifferentWhenUsingFactory(): void
    {
        $this->container->bindFactory($this->baseClass, function () {
            return new $this->concreteFoo;
        });
        $instance1 = $this->container->resolve($this->baseClass);
        $instance2 = $this->container->resolve($this->baseClass);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testMixOfConcreteClassesAndPrimitivesInConstructorForPrototype(): void
    {
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $sharedInstance */
        $this->container->bindPrototype($this->constructorWithConcreteClassesAndPrimitives, null, [23]);
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $instance */
        $instance = $this->container->resolve($this->constructorWithConcreteClassesAndPrimitives);
        $this->assertInstanceOf($this->constructorWithConcreteClassesAndPrimitives, $instance);
        $this->assertEquals(23, $instance->getId());
        $this->assertNotSame($instance, $this->container->resolve($this->constructorWithConcreteClassesAndPrimitives));
    }

    public function testMixOfConcreteClassesAndPrimitivesInConstructorForSingleton(): void
    {
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $instance */
        $this->container->bindSingleton($this->constructorWithConcreteClassesAndPrimitives, null, [23]);
        $instance = $this->container->resolve($this->constructorWithConcreteClassesAndPrimitives);
        /** @var ConstructorWithMixOfConcreteClassesAndPrimitives $newInstance */
        $this->assertInstanceOf($this->constructorWithConcreteClassesAndPrimitives, $instance);
        $this->assertEquals(23, $instance->getId());
        $this->assertSame($instance, $this->container->resolve($this->constructorWithConcreteClassesAndPrimitives));
    }

    public function testMultipleTargetedBindings(): void
    {
        $this->container->for('baz', function (IContainer $container) {
            $container->bindSingleton(['foo', 'bar'], $this->concreteFoo);
        });
        $this->assertTrue($this->container->for('baz', function (IContainer $container) {
            return $container->hasBinding('foo');
        }));
        $this->assertTrue($this->container->for('baz', function (IContainer $container) {
            return $container->hasBinding('bar');
        }));
    }

    public function testMultipleUniversalBindings(): void
    {
        $this->container->bindSingleton(['foo', 'bar'], $this->concreteFoo);
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
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindFactory($this->fooInterface, function () {
                return new $this->concreteFoo;
            });
        });
        $instance = $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $this->assertInstanceOf($this->concreteFoo, $instance);
    }

    public function testResolvingPrototypeForTarget(): void
    {
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindPrototype($this->fooInterface, $this->concreteFoo);
        });
        $instance1 = $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $instance2 = $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $this->assertInstanceOf($this->concreteFoo, $instance1);
        $this->assertInstanceOf($this->concreteFoo, $instance2);
        $this->assertNotSame($instance1, $instance2);
    }

    public function testResolvingPrototypeNonExistentClass(): void
    {
        $this->expectException(ResolutionException::class);
        $this->container->resolve('DoesNotExist');
    }

    public function testResolvingSingletonForTarget(): void
    {
        $this->container->for('foo', function (IContainer $container) {
            $container->bindSingleton($this->fooInterface, $this->concreteFoo);
        });
        $instance1 = $this->container->for('foo', function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $instance2 = $this->container->for('foo', function (IContainer $container) {
            return $container->resolve($this->fooInterface);
        });
        $this->assertInstanceOf($this->concreteFoo, $instance1);
        $this->assertInstanceOf($this->concreteFoo, $instance2);
        $this->assertSame($instance1, $instance2);

        // Make sure that the singleton is not bound universally
        try {
            $this->container->resolve($this->constructorWithIFoo);
            // The line above should throw an exception, so fail if we've gotten here
            $this->fail('Targeted singleton accidentally bound universally');
        } catch (DependencyInjectionException $ex) {
            // Don't do anything
        }
    }

    public function testSingletonDependenciesInPrototype(): void
    {
        $this->container->bindPrototype($this->constructorWithInterfacesAndPrimitives, null, [23]);
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $this->container->bindSingleton($this->personInterface, $this->concretePerson);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        $this->assertSame($instance1->getFoo(), $instance2->getFoo());
        $this->assertSame($instance1->getPerson(), $instance2->getPerson());
        $this->assertNotSame($instance1, $instance2);
    }

    public function testSingletonDependenciesInSingleton(): void
    {
        $this->container->bindSingleton($this->constructorWithInterfacesAndPrimitives, null, [23]);
        $this->container->bindSingleton($this->fooInterface, $this->concreteFoo);
        $this->container->bindSingleton($this->personInterface, $this->concretePerson);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance1 */
        $instance1 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        /** @var ConstructorWithMixOfInterfacesAndPrimitives $instance2 */
        $instance2 = $this->container->resolve($this->constructorWithInterfacesAndPrimitives);
        $this->assertInstanceOf($this->constructorWithInterfacesAndPrimitives, $instance1);
        $this->assertSame($instance1, $instance2);
        $this->assertEquals(23, $instance1->getId());
        $this->assertEquals(23, $instance2->getId());
        $this->assertSame($instance1->getPerson(), $instance2->getPerson());
    }

    public function testTargetedBindingOfInstanceToInterface(): void
    {
        $targetedInstance = new $this->concreteFoo();
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) use ($targetedInstance) {
            $container->bindInstance($this->fooInterface, $targetedInstance);
        });
        // This universal binding should NOT take precedence over the class binding
        $this->container->bindPrototype($this->fooInterface, $this->secondConcreteIFoo);
        $resolvedInstance = $this->container->resolve($this->constructorWithIFoo);
        $this->assertSame($targetedInstance, $resolvedInstance->getFoo());
    }

    public function testTargetedFactoryBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for('foo', function (IContainer $container) {
            $container->bindFactory($this->fooInterface, function () {
                return new $this->concreteFoo();
            });
        });
        $this->container->for('bar', function (IContainer $container) {
            $container->bindFactory('doesNotExist', function () {
                return new $this->concreteFoo();
            });
        });
        $this->assertFalse($this->container->for('foo', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for('bar', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedInstanceBindingsOnlyApplyToNextCall(): void
    {
        $instance1 = new $this->concreteFoo();
        $instance2 = new $this->concreteFoo();
        $this->container->for('foo', function (IContainer $container) use ($instance1) {
            $container->bindInstance($this->fooInterface, $instance1);
        });
        $this->container->for('bar', function (IContainer $container) use ($instance2) {
            $container->bindInstance('doesNotExist', $instance2);
        });
        $this->assertFalse($this->container->for('foo', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for('bar', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedPrototypeBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for('foo', function (IContainer $container) {
            $container->bindPrototype($this->fooInterface, 'bar');
        });
        $this->container->for('baz', function (IContainer $container) {
            $container->bindPrototype('doesNotExist', 'bar');
        });
        $this->assertFalse($this->container->for('foo', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for('baz', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
    }

    public function testTargetedSingletonBindingsOnlyApplyToNextCall(): void
    {
        $this->container->for('foo', function (IContainer $container) {
            $container->bindSingleton($this->fooInterface, 'bar');
        });
        $this->container->for('baz', function (IContainer $container) {
            $container->bindSingleton('doesNotExist', 'bar');
        });
        $this->assertFalse($this->container->for('foo', function (IContainer $container) {
            return $container->hasBinding('doesNotExist');
        }));
        $this->assertTrue($this->container->for('baz', function (IContainer $container) {
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

    public function testUnbindingFactory(): void
    {
        $this->container->bindFactory($this->baseClass, function () {
            return new $this->concreteFoo;
        });
        $this->container->unbind($this->baseClass);
        $this->assertFalse($this->container->hasBinding($this->baseClass));
    }

    public function testUnbindingMultipleInterfaces(): void
    {
        $this->container->bindSingleton('foo', 'bar');
        $this->container->bindSingleton('baz', 'blah');
        $this->container->unbind(['foo', 'baz']);
        $this->assertFalse($this->container->hasBinding('foo'));
        $this->assertFalse($this->container->hasBinding('baz'));
    }

    public function testUnbindingTargetedBinding(): void
    {
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->bindPrototype($this->fooInterface, $this->concreteFoo);
        });
        $this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            $container->unbind($this->fooInterface);
        });
        $this->assertFalse($this->container->for($this->constructorWithIFoo, function (IContainer $container) {
            return $container->hasBinding($this->fooInterface);
        }));
    }

    public function testUnbindingUniversalBinding(): void
    {
        $this->container->bindPrototype($this->fooInterface, $this->concreteFoo);
        $this->container->unbind($this->fooInterface);
        $this->assertFalse($this->container->hasBinding($this->fooInterface));
    }

    public function testUniversallyBindingInstanceToInterface(): void
    {
        $instance = new $this->concreteFoo();
        $this->container->bindInstance($this->fooInterface, $instance);
        $this->assertSame($instance, $this->container->resolve($this->fooInterface));
        $this->assertNotSame($instance, $this->container->resolve($this->concreteFoo));
    }
}

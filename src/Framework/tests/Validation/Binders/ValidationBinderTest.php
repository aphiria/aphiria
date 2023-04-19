<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Validation\Binders\ValidationBinder;
use Aphiria\Validation\Constraints\Attributes\AttributeObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Caching\FileObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\Caching\IObjectConstraintsRegistryCache;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use Aphiria\Validation\ErrorMessages\DefaultErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\IcuFormatErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageInterpolator;
use Aphiria\Validation\ErrorMessages\IErrorMessageTemplateRegistry;
use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageInterpolator;
use Aphiria\Validation\IValidator;
use Aphiria\Validation\Validator;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ValidationBinderTest extends TestCase
{
    private ValidationBinder $binder;
    private IContainer&MockInterface $container;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new ValidationBinder();
        $this->container = Mockery::mock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = \getenv('APP_ENV') ?: null;
    }

    protected function tearDown(): void
    {
        Mockery::close();

        // Restore the environment name
        if ($this->currEnvironment === null) {
            \putenv('APP_ENV=');
        } else {
            \putenv("APP_ENV={$this->currEnvironment}");
        }
    }

    public function testAttributeRegistrantIsRegistered(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, IErrorMessageInterpolator::class],
            [AttributeObjectConstraintsRegistrant::class, AttributeObjectConstraintsRegistrant::class]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testConstraintCacheIsUsedInProd(): void
    {
        // Basically just ensuring we cover the production case in this test
        \putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, StringReplaceErrorMessageInterpolator::class]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testCustomErrorMessageRegistriesAreResolved(): void
    {
        $errorMessageTemplates = Mockery::mock(IErrorMessageTemplateRegistry::class);
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => $errorMessageTemplates::class
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, StringReplaceErrorMessageInterpolator::class]
        ]);
        $this->container->shouldReceive('resolve')
            ->with($errorMessageTemplates::class)
            ->andReturn($errorMessageTemplates);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testDefaultErrorMessageRegistryIsSupported(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => DefaultErrorMessageTemplateRegistry::class
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, StringReplaceErrorMessageInterpolator::class]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testIcuFormatErrorMessageInterpolatorIsSupported(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, StringReplaceErrorMessageInterpolator::class]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testInvalidErrorMessageTemplateTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Error message template must be instance of ' . IErrorMessageTemplateRegistry::class);
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => self::class
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, StringReplaceErrorMessageInterpolator::class]
        ]);
        $this->container->shouldReceive('resolve')
            ->with(self::class)
            ->andReturn($this);
        $this->binder->bind($this->container);
    }

    public function testMissingErrorMessageTemplateTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing key "type" from error message template config');
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
    }

    public function testStringReplaceErrorMessageInterpolatorIsSupported(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageInterpolator'] = [
            'type' => IcuFormatErrorMessageInterpolator::class,
            'defaultLocale' => 'en'
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, IcuFormatErrorMessageInterpolator::class]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnknownErrorMessageInterpolatorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported error message interpolator type foo');
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageInterpolator'] = [
            'type' => 'foo'
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, 'foo']
        ]);
        $this->binder->bind($this->container);
    }

    /**
     * Gets the base config
     *
     * @return array<string, mixed> The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'validation' => [
                    'attributePaths' => ['/src'],
                    'constraintsCachePath' => '/cache',
                    'errorMessageInterpolator' => [
                        'type' => StringReplaceErrorMessageInterpolator::class
                    ]
                ]
            ]
        ];
    }

    /**
     * Sets up the container mock
     *
     * @param list<array> $additionalParameters The additional parameters to configure
     */
    private function setUpContainerMock(array $additionalParameters = []): void
    {
        $parameters = [
            [ObjectConstraintsRegistry::class, ObjectConstraintsRegistry::class],
            [[IValidator::class, Validator::class], Validator::class],
            [IObjectConstraintsRegistryCache::class, FileObjectConstraintsRegistryCache::class],
            [ObjectConstraintsRegistrantCollection::class, ObjectConstraintsRegistrantCollection::class],
            [AttributeObjectConstraintsRegistrant::class, AttributeObjectConstraintsRegistrant::class]
        ];

        foreach ($additionalParameters as $additionalParameter) {
            $parameters[] = $additionalParameter;
        }

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }
}

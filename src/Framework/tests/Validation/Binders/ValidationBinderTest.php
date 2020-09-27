<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Validation\Binders\ValidationBinder;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValidationBinderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    private ValidationBinder $binder;
    private ?string $currEnvironment;

    protected function setUp(): void
    {
        $this->binder = new ValidationBinder();
        $this->container = $this->createMock(IContainer::class);
        GlobalConfiguration::resetConfigurationSources();
        $this->currEnvironment = getenv('APP_ENV') ?: null;
    }

    protected function tearDown(): void
    {
        // Restore the environment name
        if ($this->currEnvironment !== null) {
            putenv("APP_ENV={$this->currEnvironment}");
        }
    }

    public function testAnnotationRegistrantIsRegistered(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, $this->isInstanceOf(IErrorMessageInterpolator::class)],
            [AnnotationObjectConstraintsRegistrant::class, $this->isInstanceOf(AnnotationObjectConstraintsRegistrant::class)]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testConstraintCacheIsUsedInProd(): void
    {
        // Basically just ensuring we cover the production case in this test
        putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testCustomErrorMessageRegistriesAreResolved(): void
    {
        $errorMessageTemplates = $this->createMock(IErrorMessageTemplateRegistry::class);
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => \get_class($errorMessageTemplates)
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMock();
        $this->container->method('resolve')
            ->with(\get_class($errorMessageTemplates))
            ->willReturn($errorMessageTemplates);
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
        $this->setUpContainerMock();
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testIcuFormatErrorMessageInterpolatorIsSupported(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->setUpContainerMock([
            [IErrorMessageInterpolator::class, $this->isInstanceOf(StringReplaceErrorMessageInterpolator::class)]
        ]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
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
            [IErrorMessageInterpolator::class, $this->isInstanceOf(IcuFormatErrorMessageInterpolator::class)]
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
        $this->binder->bind($this->container);
    }

    /**
     * Gets the base config
     *
     * @return array The base config
     */
    private static function getBaseConfig(): array
    {
        return [
            'aphiria' => [
                'validation' => [
                    'annotationPaths' => ['/src'],
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
     * @param array[] $additionalParameters The additional parameters to configure
     */
    private function setUpContainerMock(array $additionalParameters = []): void
    {
        $parameters = [
            [ObjectConstraintsRegistry::class, $this->isInstanceOf(ObjectConstraintsRegistry::class)],
            [[IValidator::class, Validator::class], $this->isInstanceOf(Validator::class)],
            [IObjectConstraintsRegistryCache::class, $this->isInstanceOf(FileObjectConstraintsRegistryCache::class)],
            [ObjectConstraintsRegistrantCollection::class, $this->isInstanceOf(ObjectConstraintsRegistrantCollection::class)]
        ];

        foreach ($additionalParameters as $additionalParameter) {
            $parameters[] = $additionalParameter;
        }

        $this->container->method('bindInstance')
            ->withConsecutive(...$parameters);
    }
}

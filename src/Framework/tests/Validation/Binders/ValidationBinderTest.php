<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Validation\Binders;

use Aphiria\Configuration\GlobalConfiguration;
use Aphiria\Configuration\HashTableConfiguration;
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

        // Some universal assertions
        $this->container->expects($this->at(0))
            ->method('bindInstance')
            ->with(ObjectConstraintsRegistry::class, $this->isInstanceOf(ObjectConstraintsRegistry::class));
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with([IValidator::class, Validator::class], $this->isInstanceOf(Validator::class));
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(IObjectConstraintsRegistryCache::class, $this->isInstanceOf(FileObjectConstraintsRegistryCache::class));
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(ObjectConstraintsRegistrantCollection::class, $this->isInstanceOf(ObjectConstraintsRegistrantCollection::class));
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
        $this->container->expects($this->at(5))
            ->method('bindInstance')
            ->with(AnnotationObjectConstraintsRegistrant::class, $this->isInstanceOf(AnnotationObjectConstraintsRegistrant::class));
        $this->binder->bind($this->container);
    }

    public function testConstraintCacheIsUsedInProd(): void
    {
        // Basically just ensuring we cover the production case in this test
        putenv('APP_ENV=production');
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
    }

    public function testCustomErrorMessageRegistriesAreResolved(): void
    {
        $errorMessageTemplates = $this->createMock(IErrorMessageTemplateRegistry::class);
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => \get_class($errorMessageTemplates)
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->expects($this->at(4))
            ->method('resolve')
            ->with(\get_class($errorMessageTemplates))
            ->willReturn($errorMessageTemplates);
        $this->binder->bind($this->container);
    }

    public function testDefaultErrorMessageRegistryIsSupported(): void
    {
        $config = self::getBaseConfig();
        $config['aphiria']['validation']['errorMessageTemplates'] = [
            'type' => DefaultErrorMessageTemplateRegistry::class
        ];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->binder->bind($this->container);
    }

    public function testIcuFormatErrorMessageInterpolatorIsSupported(): void
    {
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->container->expects($this->at(4))
            ->method('bindInstance')
            ->with(IErrorMessageInterpolator::class, $this->isInstanceOf(StringReplaceErrorMessageInterpolator::class));
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
        $this->container->expects($this->at(4))
            ->method('bindInstance')
            ->with(IErrorMessageInterpolator::class, $this->isInstanceOf(IcuFormatErrorMessageInterpolator::class));
        $this->binder->bind($this->container);
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
}

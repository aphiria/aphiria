<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\ContentNegotiation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\ContentNegotiation\BodyNegotiator;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IBodyNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\IEncodingMatcher;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\ContentNegotiation\Binders\ContentNegotiationBinder;
use Aphiria\Net\Http\IResponseFactory;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ContentNegotiationBinderTest extends TestCase
{
    private IContainer&MockInterface $container;
    private ContentNegotiationBinder $binder;

    protected function setUp(): void
    {
        $this->container = Mockery::mock(IContainer::class);
        $this->binder = new ContentNegotiationBinder();
        GlobalConfiguration::resetConfigurationSources();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testAcceptCharsetEncodingMatcherIsCreatedDirectly(): void
    {
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, AcceptCharsetEncodingMatcher::class],
            [ILanguageMatcher::class, AcceptLanguageMatcher::class],
            [IContentNegotiator::class, ContentNegotiator::class],
            [IBodyNegotiator::class, BodyNegotiator::class],
            [IResponseFactory::class, NegotiatedResponseFactory::class]
        ]);
        $this->setUpContainerMockResolve();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testAcceptLanguageMatcherIsCreatedDirectly(): void
    {
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, AcceptCharsetEncodingMatcher::class],
            [ILanguageMatcher::class, AcceptLanguageMatcher::class],
            [IContentNegotiator::class, ContentNegotiator::class],
            [IBodyNegotiator::class, BodyNegotiator::class],
            [IResponseFactory::class, NegotiatedResponseFactory::class]
        ]);
        $this->setUpContainerMockResolve();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testContentNegotiatorIsBound(): void
    {
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, AcceptCharsetEncodingMatcher::class],
            [ILanguageMatcher::class, AcceptLanguageMatcher::class],
            [IContentNegotiator::class, ContentNegotiator::class],
            [IBodyNegotiator::class, BodyNegotiator::class],
            [IResponseFactory::class, NegotiatedResponseFactory::class]
        ]);
        $this->setUpContainerMockResolve();
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration(self::getBaseConfig()));
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testInvalidEncodingMatcherThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Encoding matcher must implement ' . IEncodingMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['encodingMatcher'] = self::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class]
        ]);
        $this->setUpContainerMockResolve([self::class, $this]);
        $this->binder->bind($this->container);
    }

    public function testInvalidLanguageMatcherThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Language matcher must implement ' . ILanguageMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['languageMatcher'] = self::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, AcceptCharsetEncodingMatcher::class]
        ]);
        $this->setUpContainerMockResolve([self::class, $this]);
        $this->binder->bind($this->container);
    }

    public function testMediaTypeFormatterThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Media type formatters must implement ' . IMediaTypeFormatter::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['mediaTypeFormatters'] = [self::class];
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->container->shouldReceive('resolve')
            ->with(self::class)
            ->andReturn($this);
        $this->binder->bind($this->container);
    }

    public function testUnknownEncodingMatchersAreResolved(): void
    {
        $encodingMatcher = Mockery::mock(IEncodingMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['encodingMatcher'] = $encodingMatcher::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, $encodingMatcher::class],
            [ILanguageMatcher::class, AcceptLanguageMatcher::class],
            [IContentNegotiator::class, ContentNegotiator::class],
            [IBodyNegotiator::class, BodyNegotiator::class],
            [IResponseFactory::class, NegotiatedResponseFactory::class]
        ]);
        $this->setUpContainerMockResolve([$encodingMatcher::class, $encodingMatcher]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnknownLanguageMatchersAreResolved(): void
    {
        $languageMatcher = Mockery::mock(ILanguageMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['languageMatcher'] = $languageMatcher::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, MediaTypeFormatterMatcher::class],
            [IEncodingMatcher::class, AcceptCharsetEncodingMatcher::class],
            [ILanguageMatcher::class, $languageMatcher::class],
            [IContentNegotiator::class, ContentNegotiator::class],
            [IBodyNegotiator::class, BodyNegotiator::class],
            [IResponseFactory::class, NegotiatedResponseFactory::class]
        ]);
        $this->setUpContainerMockResolve([$languageMatcher::class, $languageMatcher]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
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
                'contentNegotiation' => [
                    'encodingMatcher' => AcceptCharsetEncodingMatcher::class,
                    'languageMatcher' => AcceptLanguageMatcher::class,
                    'mediaTypeFormatters' => [
                        JsonMediaTypeFormatter::class
                    ],
                    'supportedLanguages' => ['en']
                ]
            ]
        ];
    }

    /**
     * Sets up tbe bindInstance() calls on the container mock
     *
     * @param list<array> $parameters The parameters to pass in
     */
    private function setUpContainerMockBindInstance(array $parameters): void
    {
        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('bindInstance')
                ->with($parameter[0], Mockery::type($parameter[1]));
        }
    }

    /**
     * Sets up tbe resolve() calls on the container mock
     *
     * @param array|null $additionalParameters The additional parameters to set up in the mock, or null if none
     */
    private function setUpContainerMockResolve(array $additionalParameters = null): void
    {
        $parameters = [
            [JsonMediaTypeFormatter::class, new JsonMediaTypeFormatter()]
        ];

        if ($additionalParameters !== null) {
            $parameters[] = $additionalParameters;
        }

        foreach ($parameters as $parameter) {
            $this->container->shouldReceive('resolve')
                ->with($parameter[0])
                ->andReturn($parameter[1]);
        }
    }
}

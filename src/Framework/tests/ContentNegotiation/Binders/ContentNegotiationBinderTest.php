<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\ContentNegotiation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\HashTableConfiguration;
use Aphiria\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\IEncodingMatcher;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\ContentNegotiation\Binders\ContentNegotiationBinder;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentNegotiationBinderTest extends TestCase
{
    private IContainer|MockObject $container;
    private ContentNegotiationBinder $binder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->binder = new ContentNegotiationBinder();
        GlobalConfiguration::resetConfigurationSources();
    }

    public function testAcceptCharsetEncodingMatcherIsCreatedDirectly(): void
    {
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $this->isInstanceOf(AcceptCharsetEncodingMatcher::class)],
            [ILanguageMatcher::class, $this->isInstanceOf(AcceptLanguageMatcher::class)],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
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
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $this->isInstanceOf(AcceptCharsetEncodingMatcher::class)],
            [ILanguageMatcher::class, $this->isInstanceOf(AcceptLanguageMatcher::class)],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
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
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $this->isInstanceOf(AcceptCharsetEncodingMatcher::class)],
            [ILanguageMatcher::class, $this->isInstanceOf(AcceptLanguageMatcher::class)],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
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
        $this->container->method('resolve')
            ->with(self::class)
            ->willReturn($this);
        $this->binder->bind($this->container);
    }

    public function testUnknownEncodingMatchersAreResolved(): void
    {
        $encodingMatcher = $this->createMock(IEncodingMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['encodingMatcher'] = $encodingMatcher::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $encodingMatcher],
            [ILanguageMatcher::class, $this->isInstanceOf(AcceptLanguageMatcher::class)],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
        ]);
        $this->setUpContainerMockResolve([$encodingMatcher::class, $encodingMatcher]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnknownLanguageMatchersAreResolved(): void
    {
        $languageMatcher = $this->createMock(ILanguageMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['languageMatcher'] = $languageMatcher::class;
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $this->isInstanceOf(AcceptCharsetEncodingMatcher::class)],
            [ILanguageMatcher::class, $languageMatcher],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
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
        $this->container->method('bindInstance')
            ->withConsecutive(...$parameters);
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

        $this->container->method('resolve')
            ->willReturnMap($parameters);
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\ContentNegotiation\Binders\ContentNegotiationBinder;
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

    public function testUnknownEncodingMatchersAreResolved(): void
    {
        $encodingMatcher = $this->createMock(IEncodingMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['encodingMatcher'] = \get_class($encodingMatcher);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $encodingMatcher],
            [ILanguageMatcher::class, $this->isInstanceOf(AcceptLanguageMatcher::class)],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
        ]);
        $this->setUpContainerMockResolve([\get_class($encodingMatcher), $encodingMatcher]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testUnknownLanguageMatchersAreResolved(): void
    {
        $languageMatcher = $this->createMock(ILanguageMatcher::class);
        $config = self::getBaseConfig();
        $config['aphiria']['contentNegotiation']['languageMatcher'] = \get_class($languageMatcher);
        GlobalConfiguration::addConfigurationSource(new HashTableConfiguration($config));
        $this->setUpContainerMockBindInstance([
            [IMediaTypeFormatterMatcher::class, $this->isInstanceOf(MediaTypeFormatterMatcher::class)],
            [IEncodingMatcher::class, $this->isInstanceOf(AcceptCharsetEncodingMatcher::class)],
            [ILanguageMatcher::class, $languageMatcher],
            [IContentNegotiator::class, $this->isInstanceOf(ContentNegotiator::class)]
        ]);
        $this->setUpContainerMockResolve([\get_class($languageMatcher), $languageMatcher]);
        $this->binder->bind($this->container);
        // Dummy assertion
        $this->assertTrue(true);
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
     * @param array[] $parameters The parameters to pass in
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

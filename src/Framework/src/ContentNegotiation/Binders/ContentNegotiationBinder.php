<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\ContentNegotiation\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\Application\Configuration\MissingConfigurationValueException;
use Aphiria\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\IEncodingMatcher;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IResponseFactory;
use InvalidArgumentException;

/**
 * Defines the content negotiation binder
 */
class ContentNegotiationBinder extends Binder
{
    /**
     * @inheritdoc
     * @throws MissingConfigurationValueException Thrown if required config values were not set
     * @throws InvalidArgumentException Thrown if any of the config values are not of the expected type
     */
    public function bind(IContainer $container): void
    {
        /** @var list<IMediaTypeFormatter> $mediaTypeFormatters */
        $mediaTypeFormatters = \array_map(
            static function (string $class) use ($container): IMediaTypeFormatter {
                /** @var class-string<IMediaTypeFormatter> $class */
                $mediaTypeFormatter = $container->resolve($class);

                if (!$mediaTypeFormatter instanceof IMediaTypeFormatter) {
                    throw new InvalidArgumentException('Media type formatters must implement ' . IMediaTypeFormatter::class);
                }

                return $mediaTypeFormatter;
            },
            GlobalConfiguration::getArray('aphiria.contentNegotiation.mediaTypeFormatters')
        );
        $mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher($mediaTypeFormatters);
        $container->bindInstance(IMediaTypeFormatterMatcher::class, $mediaTypeFormatterMatcher);

        /** @var class-string<IEncodingMatcher> $encodingMatcherName */
        $encodingMatcherName = GlobalConfiguration::getString('aphiria.contentNegotiation.encodingMatcher');

        if ($encodingMatcherName === AcceptCharsetEncodingMatcher::class) {
            $encodingMatcher = new AcceptCharsetEncodingMatcher();
        } else {
            $encodingMatcher = $container->resolve($encodingMatcherName);
        }

        if (!$encodingMatcher instanceof IEncodingMatcher) {
            throw new InvalidArgumentException('Encoding matcher must implement ' . IEncodingMatcher::class);
        }

        $container->bindInstance(IEncodingMatcher::class, $encodingMatcher);

        /** @var class-string<ILanguageMatcher> $languageMatcherName */
        $languageMatcherName = GlobalConfiguration::getString('aphiria.contentNegotiation.languageMatcher');

        if ($languageMatcherName === AcceptLanguageMatcher::class) {
            /** @var list<string> $supportedLanguages */
            $supportedLanguages = GlobalConfiguration::getArray('aphiria.contentNegotiation.supportedLanguages');
            $languageMatcher = new AcceptLanguageMatcher($supportedLanguages);
        } else {
            $languageMatcher = $container->resolve($languageMatcherName);
        }

        if (!$languageMatcher instanceof ILanguageMatcher) {
            throw new InvalidArgumentException('Language matcher must implement ' . ILanguageMatcher::class);
        }

        $container->bindInstance(ILanguageMatcher::class, $languageMatcher);

        $contentNegotiator = new ContentNegotiator(
            $mediaTypeFormatters,
            $mediaTypeFormatterMatcher,
            $encodingMatcher,
            $languageMatcher
        );
        $responseFactory = new NegotiatedResponseFactory($contentNegotiator);
        $container->bindInstance(IContentNegotiator::class, $contentNegotiator);
        $container->bindInstance(IResponseFactory::class, $responseFactory);
    }
}

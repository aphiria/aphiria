<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Net\Bootstrappers;

use Aphiria\Configuration\Configuration;
use Aphiria\Configuration\ConfigurationException;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\Net\Http\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\Net\Http\ContentNegotiation\ContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\IEncodingMatcher;
use Aphiria\Net\Http\ContentNegotiation\ILanguageMatcher;
use Aphiria\Net\Http\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\NegotiatedResponseFactory;

/**
 * Defines the content negotiator bootstrapper
 */
final class ContentNegotiatorBootstrapper extends Bootstrapper
{
    /**
     * @inheritdoc
     * @throws ConfigurationException Thrown if the config is missing values
     */
    public function registerBindings(IContainer $container): void
    {
        /**
         * ----------------------------------------------------------
         * Media type formatter matcher
         * ----------------------------------------------------------
         *
         * Configure how you want media type formatters to be matched.
         * Note: The first registered media type formatter will be considered the default one
         */
        $mediaTypeFormatters = array_map(
            fn (string $class) => $container->resolve($class),
            Configuration::getArray('aphiria.contentNegotiation.mediaTypeFormatters')
        );
        $mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher($mediaTypeFormatters);
        $container->bindInstance(IMediaTypeFormatterMatcher::class, $mediaTypeFormatterMatcher);

        /**
         * ----------------------------------------------------------
         * Encoding matcher
         * ----------------------------------------------------------
         *
         * Configure how you want encodings to be matched.
         * Default: Use the Accept-Charset header
         * @link https://tools.ietf.org/html/rfc5646
         */
        $encodingMatcherName = Configuration::getString('aphiria.contentNegotiation.encodingMatcher');

        if ($encodingMatcherName === AcceptCharsetEncodingMatcher::class) {
            $encodingMatcher = new AcceptCharsetEncodingMatcher();
        } else {
            $encodingMatcher = $container->resolve($encodingMatcherName);
        }

        $container->bindInstance(IEncodingMatcher::class, $encodingMatcher);

        /**
         * ----------------------------------------------------------
         * Language matcher
         * ----------------------------------------------------------
         *
         * Configure how you want languages to be matched.  The supported languages must follow RFC 5646.
         * Default: Use the Accept-Language header
         * @link https://tools.ietf.org/html/rfc5646
         */
        $languageMatcherName = Configuration::getString('aphiria.contentNegotiation.languageMatcher');

        if ($languageMatcherName === AcceptLanguageMatcher::class) {
            $languageMatcher = new AcceptLanguageMatcher(Configuration::getArray('aphiria.contentNegotiation.supportedLanguages'));
        } else {
            $languageMatcher = $container->resolve($languageMatcherName);
        }

        $container->bindInstance(ILanguageMatcher::class, $languageMatcher);

        $contentNegotiator = new ContentNegotiator(
            $mediaTypeFormatters,
            $mediaTypeFormatterMatcher,
            $encodingMatcher,
            $languageMatcher
        );
        $container->bindInstance(IContentNegotiator::class, $contentNegotiator);
        $container->bindInstance(INegotiatedResponseFactory::class, new NegotiatedResponseFactory($contentNegotiator));
    }
}

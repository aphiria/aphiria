<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Net\Binders;

use Aphiria\Application\Configuration\GlobalConfiguration;
use Aphiria\ContentNegotiation\AcceptCharsetEncodingMatcher;
use Aphiria\ContentNegotiation\AcceptLanguageMatcher;
use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\IEncodingMatcher;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\NegotiatedResponseFactory;
use Aphiria\DependencyInjection\Binders\Binder;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Net\Http\IResponseFactory;

/**
 * Defines the content negotiation binder
 */
class ContentNegotiationBinder extends Binder
{
    /**
     * @inheritdoc
     */
    public function bind(IContainer $container): void
    {
        $mediaTypeFormatters = array_map(
            fn (string $class) => $container->resolve($class),
            GlobalConfiguration::getArray('aphiria.contentNegotiation.mediaTypeFormatters')
        );
        $mediaTypeFormatterMatcher = new MediaTypeFormatterMatcher($mediaTypeFormatters);
        $container->bindInstance(IMediaTypeFormatterMatcher::class, $mediaTypeFormatterMatcher);

        $encodingMatcherName = GlobalConfiguration::getString('aphiria.contentNegotiation.encodingMatcher');

        if ($encodingMatcherName === AcceptCharsetEncodingMatcher::class) {
            $encodingMatcher = new AcceptCharsetEncodingMatcher();
        } else {
            $encodingMatcher = $container->resolve($encodingMatcherName);
        }

        $container->bindInstance(IEncodingMatcher::class, $encodingMatcher);

        $languageMatcherName = GlobalConfiguration::getString('aphiria.contentNegotiation.languageMatcher');

        if ($languageMatcherName === AcceptLanguageMatcher::class) {
            $languageMatcher = new AcceptLanguageMatcher(GlobalConfiguration::getArray('aphiria.contentNegotiation.supportedLanguages'));
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
        $responseFactory = new NegotiatedResponseFactory($contentNegotiator);
        $container->bindInstance(IContentNegotiator::class, $contentNegotiator);
        $container->bindInstance(IResponseFactory::class, $responseFactory);
    }
}

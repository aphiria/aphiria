<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;

/**
 * Defines a factory for context factories
 */
class RequestContextFactory
{
    /** @var IContentNegotiator The content negotiator to use */
    private $contentNegotiator;

    /**
     * @param IContentNegotiator $contentNegotiator The context negotiator to use
     */
    public function __construct(IContentNegotiator $contentNegotiator)
    {
        $this->contentNegotiator = $contentNegotiator;
    }

    /**
     * Creates a request context from a request
     *
     * @param IHttpRequestMessage $request The current request
     * @return RequestContext The created context
     */
    public function createRequestContext(IHttpRequestMessage $request): RequestContext
    {
        return new RequestContext(
            $request,
            $this->contentNegotiator->negotiateRequestContent($request),
            $this->contentNegotiator->negotiateResponseContent($request)
        );
    }
}
<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ResponseFactories;

use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\RequestContext;
use Opulence\Net\Uri;

/**
 * Defines a base test case for response factories to extend
 */
abstract class ResponseFactoryTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Creates a basic content negotiation result
     *
     * @return ContentNegotiationResult The content negotiation result
     */
    protected function createBasicContentNegotiationResult(): ContentNegotiationResult
    {
        return new ContentNegotiationResult(
            null,
            null,
            null,
            null
        );
    }

    /**
     * Creates a basic request context
     *
     * @return RequestContext The request context
     */
    protected function createBasicRequestContext(): RequestContext
    {
        $requestContentNegotiationResult = $this->createBasicContentNegotiationResult();
        $responseContentNegotiationResult = $this->createBasicContentNegotiationResult();

        return new RequestContext(
            new Request('GET', new Uri('http://foo.com')),
            $requestContentNegotiationResult,
            $responseContentNegotiationResult
        );
    }
}

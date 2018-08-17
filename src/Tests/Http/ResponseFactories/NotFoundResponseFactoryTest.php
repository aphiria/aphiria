<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ResponseFactories;

use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\ResponseFactories\NotFoundResponseFactory;

/**
 * Tests the not found response factory
 */
class NotFoundResponseFactoryTest extends ResponseFactoryTestCase
{
    public function testCreatingResponseUsesCorrectStatusCode(): void
    {
        $responseFactory = new NotFoundResponseFactory();
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals(HttpStatusCodes::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}

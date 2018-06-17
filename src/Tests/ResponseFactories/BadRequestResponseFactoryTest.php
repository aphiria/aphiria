<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\ResponseFactories;

use Opulence\Api\ResponseFactories\BadRequestResponseFactory;
use Opulence\Net\Http\HttpStatusCodes;

/**
 * Tests the bad request response factory
 */
class BadRequestResponseFactoryTest extends ResponseFactoryTestCase
{
    public function testCreatingResponseUsesCorrectStatusCode(): void
    {
        $responseFactory = new BadRequestResponseFactory();
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals(HttpStatusCodes::HTTP_BAD_REQUEST, $response->getStatusCode());
    }
}

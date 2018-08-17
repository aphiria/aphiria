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
use Opulence\Net\Http\ResponseFactories\OkResponseFactory;

/**
 * Tests the OK response factory
 */
class OkResponseFactoryTest extends ResponseFactoryTestCase
{
    public function testCreatingResponseUsesCorrectStatusCode(): void
    {
        $responseFactory = new OkResponseFactory();
        $response = $responseFactory->createResponse($this->createBasicRequestContext());
        $this->assertEquals(HttpStatusCodes::HTTP_OK, $response->getStatusCode());
    }
}

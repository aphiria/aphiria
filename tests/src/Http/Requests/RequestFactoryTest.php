<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

/**
 * Tests the request factory
 */
class RequestFactoryTest
{
    /** @var RequestFactory The request factory to use in tests */
    private $factory = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->factory = new RequestFactory();
    }
}

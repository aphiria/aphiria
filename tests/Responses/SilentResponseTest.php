<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses;

use Aphiria\Console\Responses\SilentResponse;
use PHPUnit\Framework\TestCase;

/**
 * Tests the silent response
 */
class SilentResponseTest extends TestCase
{
    /** @var SilentResponse The response to use in tests */
    private $response;

    public function setUp(): void
    {
        $this->response = new SilentResponse();
    }

    public function testWrite(): void
    {
        ob_start();
        $this->response->write('foo');
        $this->assertEmpty(ob_get_clean());
    }

    public function testWriteln(): void
    {
        ob_start();
        $this->response->writeln('foo');
        $this->assertEmpty(ob_get_clean());
    }
}

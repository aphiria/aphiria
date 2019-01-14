<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization\Tests\Encoding;

use Opulence\Serialization\Encoding\EncodingContext;
use Opulence\Serialization\Tests\Encoding\Mocks\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests the encoding context
 */
class EncodingContextTest extends TestCase
{
    /** @var EncodingContext The context to test */
    private $context;

    public function setUp(): void
    {
        $this->context = new EncodingContext();
    }

    public function testCheckingCircularReferenceFirstTimeForSameObjectReturnsFalse(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->assertFalse($this->context->isCircularReference($user));
    }

    public function testCheckingCircularReferenceSecondTimeForSameObjectReturnsTrue(): void
    {
        $user = new User(123, 'foo@bar.com');
        $this->context->isCircularReference($user);
        $this->assertTrue($this->context->isCircularReference($user));
    }
}

<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization\Tests\Encoding;

use Aphiria\Serialization\Encoding\EncodingContext;
use Aphiria\Serialization\Tests\Encoding\Mocks\User;
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

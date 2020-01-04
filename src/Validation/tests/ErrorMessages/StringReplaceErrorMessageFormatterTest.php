<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\ErrorMessages;

use Aphiria\Validation\ErrorMessages\StringReplaceErrorMessageFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string replacement error message formatter
 */
class StringReplaceErrorMessageFormatterTest extends TestCase
{
    private StringReplaceErrorMessageFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new StringReplaceErrorMessageFormatter();
    }

    public function testErrorMessageIdWithNoPlaceholdersIsReturnedIntact(): void
    {
        $this->assertEquals('foo bar', $this->formatter->format('foo bar'));
    }

    public function testLeftoverUnusedPlaceholdersAreRemovedFromFormattedErrorMessage(): void
    {
        $this->assertEquals('foo ', $this->formatter->format('foo {bar}'));
    }

    public function testPlaceholdersArePopulated(): void
    {
        $this->assertEquals(
            'foo dave young',
            $this->formatter->format('foo {bar} {baz}', ['bar' => 'dave', 'baz' => 'young'])
        );
    }
}

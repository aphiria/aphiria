<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Rules;

use Opulence\Routing\Matchers\Rules\UuidV4Rule;
use PHPUnit\Framework\TestCase;

/**
 * Tests the UUIDV4 rule
 */
class UuidV4RuleTest extends TestCase
{
    public function testCorrectSlugIsReturned(): void
    {
        $this->assertEquals('uuidv4', UuidV4Rule::getSlug());
    }

    public function testMatchingStringsPass(): void
    {
        $rule = new UuidV4Rule();
        $string = \random_bytes(16);
        $string[6] = \chr(\ord($string[6]) & 0x0f | 0x40);
        $string[8] = \chr(\ord($string[8]) & 0x3f | 0x80);
        $uuid = \vsprintf('%s%s-%s-%s-%s-%s%s%s', \str_split(\bin2hex($string), 4));
        $this->assertTrue($rule->passes($uuid));
        $this->assertTrue($rule->passes('{' . $uuid . '}'));
    }

    public function testNonMatchingStringsFail(): void
    {
        $rule = new UuidV4Rule();
        $this->assertFalse($rule->passes('foo'));
    }
}

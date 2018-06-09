<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Regexes;

use Opulence\Routing\Regexes\GroupRegex;
use Opulence\Routing\Regexes\GroupRegexCollection;

/**
 * Tests the group regex collection
 */
class GroupRegexCollectionTest extends \PHPUnit\Framework\TestCase
{
    /** @var GroupRegexCollection The list of regexes to test */
    private $regexes;
    /** @var string The regex for GET routes */
    private $getRegex;
    /** @var string The regex for POST routes */
    private $postRegex;

    public function setUp(): void
    {
        $this->getRegex = new GroupRegex('foo', ['bar']);
        $this->postRegex = new GroupRegex('baz', ['blah']);
        $this->regexes = new GroupRegexCollection();
        $this->regexes->add('GET', $this->getRegex);
        $this->regexes->add('POST', $this->postRegex);
    }

    public function testCloningClonesRegexes(): void
    {
        $clonedRegexes = clone $this->regexes;
        $this->assertNotSame($this->getRegex, $clonedRegexes->getByMethod('GET')[0]);
        $this->assertEquals($this->getRegex, $clonedRegexes->getByMethod('GET')[0]);
        $this->assertNotSame($this->postRegex, $clonedRegexes->getByMethod('POST')[0]);
        $this->assertEquals($this->postRegex, $clonedRegexes->getByMethod('POST')[0]);
    }

    public function testGettingByHttpMethodReturnsCorrectRegexes(): void
    {
        $this->assertEquals([$this->getRegex], $this->regexes->getByMethod('GET'));
        $this->assertEquals([$this->postRegex], $this->regexes->getByMethod('POST'));
    }
}

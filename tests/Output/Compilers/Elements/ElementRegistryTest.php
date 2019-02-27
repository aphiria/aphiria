<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Elements;

use Aphiria\Console\Output\Compilers\Elements\Element;
use Aphiria\Console\Output\Compilers\Elements\ElementRegistry;
use Aphiria\Console\Output\Compilers\Elements\Style;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the element registry
 */
class ElementRegistryTest extends TestCase
{
    /** @var ElementRegistry */
    private $elements;

    protected function setUp(): void
    {
        $this->elements = new ElementRegistry();
    }

    public function testGettingElementReturnsSameInstanceThatWasRegistered(): void
    {
        $expectedElement = new Element('foo', new Style());
        $this->elements->registerElement($expectedElement);
        $this->assertSame($expectedElement, $this->elements->getElement('foo'));
    }

    public function testGettingNonExistentElementThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->elements->getElement('foo');
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Compilers\Elements;

use Aphiria\Console\Output\Compilers\Elements\Element;
use Aphiria\Console\Output\Compilers\Elements\ElementRegistry;
use Aphiria\Console\Output\Compilers\Elements\Style;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ElementRegistryTest extends TestCase
{
    private ElementRegistry $elements;

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

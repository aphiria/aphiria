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
use Aphiria\Console\Output\Compilers\Elements\Style;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedName = 'foo';
        $expectedStyle = new Style();
        $element = new Element($expectedName, $expectedStyle);
        $this->assertSame($expectedName, $element->name);
        $this->assertSame($expectedStyle, $element->style);
    }
}

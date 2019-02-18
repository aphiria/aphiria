<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers;

use Aphiria\Console\Output\Compilers\ElementRegistrant;
use Aphiria\Console\Output\Compilers\Elements\Colors;
use Aphiria\Console\Output\Compilers\Elements\Style;
use Aphiria\Console\Output\Compilers\Elements\TextStyles;
use Aphiria\Console\Output\Compilers\IOutputCompiler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the element registrant
 */
class ElementRegistrantTest extends TestCase
{
    /** @var ElementRegistrant The registrant to use in tests */
    private $registrant;

    public function setUp(): void
    {
        $this->registrant = new ElementRegistrant();
    }

    public function testCorrectElementsAreRegistered(): void
    {
        /** @var IOutputCompiler|MockObject $compiler */
        $compiler = $this->createMock(IOutputCompiler::class);
        $compiler->expects($this->at(0))
            ->method('registerElement')
            ->with('success', new Style(Colors::BLACK, Colors::GREEN));
        $compiler->expects($this->at(1))
            ->method('registerElement')
            ->with('info', new Style(Colors::GREEN));
        $compiler->expects($this->at(2))
            ->method('registerElement')
            ->with('error', new Style(Colors::BLACK, Colors::YELLOW));
        $compiler->expects($this->at(3))
            ->method('registerElement')
            ->with('fatal', new Style(Colors::WHITE, Colors::RED));
        $compiler->expects($this->at(4))
            ->method('registerElement')
            ->with('question', new Style(Colors::WHITE, Colors::BLUE));
        $compiler->expects($this->at(5))
            ->method('registerElement')
            ->with('comment', new Style(Colors::YELLOW));
        $compiler->expects($this->at(6))
            ->method('registerElement')
            ->with('b', new Style(null, null, [TextStyles::BOLD]));
        $compiler->expects($this->at(7))
            ->method('registerElement')
            ->with('u', new Style(null, null, [TextStyles::UNDERLINE]));
        $this->registrant->registerElements($compiler);
    }
}

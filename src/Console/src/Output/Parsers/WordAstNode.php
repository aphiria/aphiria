<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

/**
 * Defines a word node
 */
final class WordAstNode extends AstNode
{
    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return false;
    }
}

<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

/**
 * Defines a tag node
 */
final class TagAstNode extends AstNode
{
    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return true;
    }
}

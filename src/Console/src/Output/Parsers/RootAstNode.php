<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

/**
 * Defines a root node
 */
final class RootAstNode extends AstNode
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return false;
    }
}

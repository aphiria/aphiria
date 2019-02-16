<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers\Parsers\Nodes;

/**
 * Defines a tag node
 */
class TagNode extends Node
{
    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return true;
    }
}

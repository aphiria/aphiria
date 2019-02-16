<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers\Parsers\Nodes\Mocks;

use Aphiria\Console\Responses\Compilers\Parsers\Nodes\Node as BaseNode;

/**
 * Mocks a node for use in testing
 */
class Node extends BaseNode
{
    /**
     * @inheritdoc
     */
    public function isTag() : bool
    {
        return false;
    }
}

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
 * Defines a root node
 */
class RootNode extends Node
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function getParent(): Node
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isTag(): bool
    {
        return false;
    }
}

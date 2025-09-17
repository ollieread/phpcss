<?php

namespace PhpCSS\Contracts;

use PhpCSS\Context;
use PhpCss\Modules\Syntax\L3\CST\Contracts\CSTNode;

interface PreludeHandler
{
    public function lower(CSTNode $cst, Context $context): Node;
}

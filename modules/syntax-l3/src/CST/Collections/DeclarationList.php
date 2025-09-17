<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST\Collections;

use PhpCss\Modules\Syntax\L3\CST\Contracts\CSTNode;

final class DeclarationList implements CSTNode
{
    /**
     * @param list<\PhpCss\Modules\Syntax\L3\CST\Declaration> $declarations
     */
    public function __construct(
        public array $declarations = [],
    )
    {
    }
}

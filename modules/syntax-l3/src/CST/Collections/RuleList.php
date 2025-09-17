<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST\Collections;

use PhpCss\Modules\Syntax\L3\CST\Contracts\CSTNode;

final readonly class RuleList implements CSTNode
{
    /**
     * @param list<\PhpCss\Modules\Syntax\L3\CST\Contracts\Rule> $rules
     */
    public function __construct(
        public array $rules,
    )
    {
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST;

use PhpCss\Modules\Syntax\L3\CST\Collections\ValueList;
use PhpCss\Modules\Syntax\L3\CST\Contracts\Rule;
use PhpCss\Modules\Syntax\L3\CST\Values\Block;

final readonly class AtRule implements Rule
{
    public function __construct(
        public string    $name,
        public ValueList $prelude,
        public ?Block    $block = null,
    )
    {
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST\Values;

use PhpCss\Modules\Syntax\L3\CST\BlockType;
use PhpCss\Modules\Syntax\L3\CST\Collections\ValueList;
use PhpCss\Modules\Syntax\L3\CST\Contracts\ComponentValue;

final readonly class Block implements ComponentValue
{
    public function __construct(
        public BlockType $type,
        public ValueList $content
    )
    {
    }
}

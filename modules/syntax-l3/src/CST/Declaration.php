<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST;

use PhpCss\Modules\Syntax\L3\CST\Collections\ValueList;
use PhpCss\Modules\Syntax\L3\CST\Contracts\CSTNode;

final readonly class Declaration implements CSTNode
{
    public function __construct(
        public string    $property,
        public ValueList $value,
        public bool      $important = false,
    )
    {
    }
}

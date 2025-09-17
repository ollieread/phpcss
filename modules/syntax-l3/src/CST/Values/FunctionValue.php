<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST\Values;

use PhpCss\Modules\Syntax\L3\CST\Collections\ValueList;
use PhpCss\Modules\Syntax\L3\CST\Contracts\ComponentValue;

final readonly class FunctionValue implements ComponentValue
{
    public function __construct(
        public string    $name,
        public ValueList $arguments,
    )
    {
    }
}

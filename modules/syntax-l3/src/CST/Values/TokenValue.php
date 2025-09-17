<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\CST\Values;

use PhpCss\Modules\Syntax\L3\CST\Contracts\ComponentValue;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;

final readonly class TokenValue implements ComponentValue
{
    public function __construct(
        public Token $token,
    )
    {
    }
}

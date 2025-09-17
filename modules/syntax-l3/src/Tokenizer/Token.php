<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer;

final readonly class Token
{
    public function __construct(
        public TokenType         $type,
        public string|float|null $value,
        public int               $offset = 0,
        public int               $length = 0,
        public array             $extra = []
    )
    {
    }
}

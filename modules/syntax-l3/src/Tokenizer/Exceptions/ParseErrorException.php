<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions;

use Exception;

final class ParseErrorException extends Exception
{
    public static function make(string $message): self
    {
        return new self('Parse Error: ' . $message);
    }
}

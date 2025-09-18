<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions;

use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use RuntimeException;
use Throwable;

final class ParseErrorException extends RuntimeException
{
    public static function make(string $message, ?Token $token = null): self
    {
        return new self(
            message: 'Parse Error: ' . $message,
            token  : $token
        );
    }

    protected ?Token $token;

    public function __construct(string $message = "", int $code = 0, ?Token $token = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->token = $token;
    }

    public function hasToken(): bool
    {
        return $this->token !== null;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }
}

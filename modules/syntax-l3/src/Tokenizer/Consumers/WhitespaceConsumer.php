<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\CodePoint;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class WhitespaceConsumer implements Consumer
{
    /**
     * Consume characters from the reader and return a token.
     *
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    public function consume(Reader $reader): Token
    {
        $whitespace = '';
        $position   = $reader->position();

        while (! $reader->eof() && CodePoint::isWhitespace($char = $reader->peek())) {
            $whitespace .= CodePoint::toCharacter($char);
            $reader->consume();
        }

        return new Token(
            TokenType::Whitespace,
            $whitespace,
            $position,
            $reader->position() - $position
        );
    }

    /**
     * Check if this consumer can consume from the current reader position.
     *
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     *
     * @return bool
     */
    public function canConsume(Reader $reader): bool
    {
        return CodePoint::isWhitespace($reader->peek());
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\CodePoint;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Sequence;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Unicode;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class StringConsumer implements Consumer
{
    /**
     * Consume characters from the reader and return a token.
     *
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     *
     * @throws \PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException
     */
    public function consume(Reader $reader): Token
    {
        $position = $reader->position();

        // Grab the single or double quote we're starting with and then
        // consume it.
        $starting = $reader->peek();
        $reader->consume();

        $value = '';
        $type  = TokenType::String;

        while (! $reader->eof()) {
            $char = $reader->peek();

            if (CodePoint::is($char, $starting)) {
                // We've hit the ending quote, so consume it and break.
                $reader->consume();
                break;
            }

            if (Sequence::isValidEscape($reader)) {
                $value .= Sequence::consumeEscape($reader);
                continue;
            }

            if (CodePoint::isNewline($char)) {
                $type = TokenType::BadString;
                // Do not consume the newline.
                break;
            }

            if ($reader->eof()) {
                // EOF before the end of the string.
                // For some reason, not a bad string?
                break;
            }
        }

        return new Token(
            $type,
            $value,
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
        return $reader->peek() === Unicode::APOSTROPHE
               || $reader->peek() === Unicode::QUOTATION_MARK;
    }
}

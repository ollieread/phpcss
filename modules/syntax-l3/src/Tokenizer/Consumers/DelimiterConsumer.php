<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\CodePoint;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Sequence;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Unicode;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class DelimiterConsumer implements Consumer
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
        $position = $reader->start();
        $char     = $reader->peek();

        // If the hash, or plus is still present, we need to eat it so it
        // doesn't get in the way.
        if ($char === Unicode::HASH || $char === Unicode::PLUS_SIGN) {
            $char = $reader->next();
        }

        if ($char === null) {
            throw ParseErrorException::make('Unexpected EOF when trying to consume a delimiter.');
        }

        $reader->consume();

        return new Token(
            TokenType::Delimiter,
            CodePoint::toCharacter($char),
            $position,
            $reader->finish()
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
        return (
                   $reader->peek() === Unicode::HASH
                   && (
                       Sequence::isValidEscape($reader, 1) === false
                       && CodePoint::isIdentChar($reader->peek(1)) === false
                   )
               ) || (
                   $reader->peek() === Unicode::PLUS_SIGN
                   && CodePoint::isDigit($reader->peek(1)) === false
               ) || (
                   $reader->peek() === Unicode::LESS_THAN
                   && Sequence::opensDelimiterComment($reader) === false
               ) || (
                   $reader->peek() === Unicode::AT
                   && Sequence::wouldStartIdent($reader, 1) === false
               );
    }
}

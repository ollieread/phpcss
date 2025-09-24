<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\CodePoint;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Unicode;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class ParenthesisConsumer implements Consumer
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

        if ($char === Unicode::LEFT_PARENTHESIS) {
            $type = TokenType::OpenParenthesis;
        } else if ($char === Unicode::RIGHT_PARENTHESIS) {
            $type = TokenType::CloseParenthesis;
        } else {
            throw ParseErrorException::make('Tried to consume a parenthesis, but no parenthesis found.');
        }

        $reader->consume();

        return new Token(
            $type,
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
        return $reader->peek() === Unicode::LEFT_PARENTHESIS
               || $reader->peek() === Unicode::RIGHT_PARENTHESIS;
    }
}

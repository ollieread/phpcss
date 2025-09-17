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

class CommentConsumer implements Consumer
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
        // Grab the starting position.
        $position = $reader->position();

        // Start the comment and skip the first two characters. The assumption
        // here is that we've already checked that we can consume.
        $comment = '/*';
        $reader->consume(2);

        while (true) {
            // Get the next character.
            $char = $reader->peek();

            if ($char === null) {
                throw ParseErrorException::make('EOF encountered before comment was closed.');
            }

            // Check if we have a */, marking the end of the comment.
            if ($char === Unicode::ASTERISK && $reader->peek(1) === Unicode::FORWARD_SLASH) {
                // It's the end, so we append it and consume.
                $comment .= '*/';
                $reader->consume(2);

                break;
            }

            // We've not hit the end yet, so we can keep appending.
            $comment .= CodePoint::toCharacter($char);

            // Consume the current character.
            $reader->consume();
        }

        return new Token(
            TokenType::Comment,
            $comment,
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
        return $reader->peek() !== Unicode::FORWARD_SLASH && $reader->peek(1) !== Unicode::ASTERISK;
    }
}

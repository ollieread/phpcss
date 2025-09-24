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

class IdentLikeConsumer implements Consumer
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
        $value    = '';
        $type     = TokenType::Ident;

        if ($reader->peek() === Unicode::AT) {
            $type = TokenType::AtKeyword;
            $reader->consume();
        } else if ($reader->peek() === Unicode::HYPHEN_MINUS) {
            $value .= '-';
            $reader->consume();
        }

        $value .= Sequence::consumeIdent($reader);

        if ($type !== TokenType::AtKeyword && $reader->peek() === Unicode::LEFT_PARENTHESIS) {
            if (CodePoint::isAsciiMatch($value, 'url')) {
                // Skip the '(' at the start of the url token.
                $reader->consume();

                // While the next two characters are whitespace, consume them.
                while (
                    $reader->eof() === false
                    && CodePoint::isWhitespace($reader->peek())
                    && CodePoint::isWhitespace($reader->peek(1))
                ) {
                    $reader->consume();
                }

                $char = $reader->peek();

                // If it's whitespace, peek ahead slightly.
                if (CodePoint::isWhitespace($char)) {
                    $char = $reader->peek(1);
                }

                if ($char !== Unicode::QUOTATION_MARK && $char !== Unicode::APOSTROPHE) {
                    // It's not a function.
                    $type = TokenType::Url;

                    // Get rid of any leading whitespace.
                    Sequence::consumeAllWhitespace($reader);

                    $url   = '';
                    $bad   = false;
                    $error = false;

                    while (true) {
                        $char = $reader->peek();

                        // If we hit the EOF, it's a parser error.
                        // But not a bad URL?????
                        if ($char === null || $reader->eof()) {
                            $error = true;
                            break;
                        }

                        // This is the end of the URL.
                        if ($char === Unicode::RIGHT_PARENTHESIS) {
                            break;
                        }

                        // If it's a backspace, it might be an escape sequence.
                        if ($char === Unicode::BACKSLASH) {
                            // If it isn't, it's a bad URL.
                            if (Sequence::isValidEscape($reader, 1) === false) {
                                $error = $bad = true;
                                break;
                            }

                            $url .= Sequence::consumeEscape($reader);
                            continue;
                        }

                        // If it's any of these, it's a bad URL and parse error.
                        if (
                            $char === Unicode::QUOTATION_MARK
                            || $char === Unicode::APOSTROPHE
                            || $char === Unicode::LEFT_PARENTHESIS
                            || CodePoint::isNonPrintable($char)
                        ) {
                            $error = $bad = true;
                            break;
                        }

                        // Is it whitespace?
                        if (CodePoint::isWhitespace($char)) {
                            // It is, so consume all of it.
                            Sequence::consumeAllWhitespace($reader);

                            $char = $reader->peek();

                            if ($char === Unicode::RIGHT_PARENTHESIS || $char === null) {
                                // Let's be lazy and force it to loop again,
                                // because both of these conditions will be
                                // caught at the start of the loop.
                                continue;
                            }

                            // It's not the end, so it's a bad URL.
                            $error = $bad = true;
                            break;
                        }

                        $url .= CodePoint::toCharacter($char);
                    }

                    // If this is set to true, it's a BAD url and we need to
                    // consume the rest of the characters until we hit a ')',
                    // otherwise it'll mess everything else up.
                    if ($bad === true) {
                        $type = TokenType::BadUrl;

                        while (true) {
                            $char = $reader->peek();

                            // A ')' means the end of the bad URL.
                            if ($char === Unicode::RIGHT_PARENTHESIS) {
                                $reader->consume();
                                break;
                            }

                            // If it's EOF, stop.
                            if ($char === null || $reader->eof()) {
                                break;
                            }

                            // If it's a backslash, and a valid escape
                            // sequence, consume the escape sequence.
                            if ($char === Unicode::BACKSLASH && Sequence::isValidEscape($reader, 1)) {
                                Sequence::consumeEscape($reader);
                                continue;
                            }

                            // Otherwise consume.
                            $reader->consume();
                        }
                    }

                    $token = new Token(
                        $type,
                        $value . $url,
                        $position,
                        $reader->finish()
                    );

                    if ($error) {
                        throw ParseErrorException::make('Invalid URL token.', $token);
                    }

                    return $token;
                }
            }

            // It's a function.
            return new Token(
                TokenType::Function,
                $value,
                $position,
                $reader->finish()
            );
        }

        // It's something else.
        return new Token(
            $type,
            $value,
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
        return Sequence::wouldStartIdent($reader)
               || (
                   (
                       $reader->peek() === Unicode::HYPHEN_MINUS
                       || $reader->peek() === Unicode::AT
                   )
                   && Sequence::wouldStartIdent($reader, 1)
               )
               || (
                   $reader->peek() === Unicode::BACKSLASH
                   && Sequence::isValidEscape($reader)
               );
    }
}

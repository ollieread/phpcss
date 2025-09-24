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

class NumericConsumer implements Consumer
{
    const string INTEGER_TYPE = 'integer';

    const string NUMBER_TYPE = 'number';

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
        $type     = self::INTEGER_TYPE;

        $start = $reader->peek();

        if ($start === Unicode::PLUS_SIGN || $start === Unicode::HYPHEN_MINUS) {
            $value .= CodePoint::toCharacter($start);
            $reader->consume();
        }

        // Keep appending until we run out of digits.
        $value .= Sequence::consumeAllDigits($reader);

        // Is there a decimal point?
        if (
            $reader->peek() === Unicode::FULL_STOP
            && CodePoint::isDigit($reader->peek(1))
        ) {
            // Decimal point followed by a digit.
            $value .= '.';
            $type  = self::NUMBER_TYPE;

            // Consume the '.'.
            $reader->consume();

            // Keep appending until we run out of digits.
            $value .= Sequence::consumeAllDigits($reader);
        } else {
            $char = $reader->peek();

            // Is it an exponent?
            if ($char === Unicode::UPPER_E || $char === Unicode::LOWER_E) {
                // 'E' or 'e' notation.
                $value .= CodePoint::toCharacter($char);
                $reader->consume();
                $next = $reader->peek();

                if ($next === Unicode::PLUS_SIGN || $next === Unicode::HYPHEN_MINUS) {
                    // + or - sign
                    $value .= CodePoint::toCharacter($next);
                    $reader->consume();

                    $next = $reader->peek();
                }

                if (CodePoint::isDigit($next)) {
                    // Keep appending until we run out of digits.
                    $value .= Sequence::consumeAllDigits($reader);
                } else {
                    // Invalid exponent.
                    throw new ParseErrorException('Invalid number, expected digit after exponent.');
                }
            }
        }

        $tokenType = TokenType::Number;
        $extra     = ['type' => $type];
        $number    = $type === self::INTEGER_TYPE ? (int)$value : (float)$value;

        if ($reader->peek() === Unicode::PERCENT_SIGN) {
            // If the next character is a %, then we're dealing with a percentage.
            $reader->consume();
            $tokenType = TokenType::Percentage;
        } else if (Sequence::wouldStartIdent($reader)) {
            // But if it starts an ident, it's a dimension.
            $extra['unit'] = Sequence::consumeIdent($reader);
            $tokenType     = TokenType::Dimension;
        } else {
            // If it's none of them, it's just a number?
            $extra['type'] = self::NUMBER_TYPE;
        }

        return new Token(
            $tokenType,
            $number,
            $position,
            $reader->finish(),
            $extra
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
        return Sequence::wouldStartNumber($reader);
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Support;

use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;

final class Sequence
{
    public static function isValidEscape(Reader $reader, int $offset = 0): bool
    {
        if ($reader->peek($offset) !== Unicode::BACKSLASH) {
            return false;
        }

        $next = $reader->peek($offset + 1);

        if ($next === null) {
            return false;
        }

        return CodePoint::isNewline($next);
    }

    public static function consumeEscape(Reader $reader): string
    {
        $value = '';
        // The first character is always a '\', so we can skip that.
        $reader->consume();
        $nextChar = $reader->peek();

        if ($nextChar === null) {
            $reader->consume();
            $value .= Unicode::REPLACEMENT_CHARACTER;
        } else if (CodePoint::isHexDigit($nextChar)) {
            $totalEscaped = 0;
            $escapedValue = 0;

            while ($totalEscaped < 6 && CodePoint::isHexDigit($reader->peek())) {
                $escapedValue = ($escapedValue << 4) | CodePoint::getHexValue($reader->peek());
                $reader->consume();
                $totalEscaped++;
            }

            if (CodePoint::isWhitespace($reader->peek())) {
                // Consume the whitespace.
                $reader->consume();
            }

            if ($escapedValue === 0 || $escapedValue > 0x10FFFF || CodePoint::isSurrogate($escapedValue)) {
                $value .= Unicode::REPLACEMENT_CHARACTER;
            } else {
                $value .= CodePoint::toCharacter($escapedValue);
            }
        } else {
            $reader->consume();
            $value .= CodePoint::toCharacter($nextChar);
        }

        return $value;
    }

    public static function wouldStartIdent(Reader $reader, int $offset = 1): bool
    {
        $first = $reader->peek($offset);

        if ($first === Unicode::HYPHEN_MINUS) {
            $second = $reader->peek($offset + 1);

            if ($second === Unicode::HYPHEN_MINUS || CodePoint::isIdentStart($second)) {
                return true;
            }

            $reader->consume();

            if (self::isValidEscape($reader, $offset)) {
                $reader->back();

                return true;
            }

            return false;
        }

        if (CodePoint::isIdentStart($first)) {
            return true;
        }

        if (self::isValidEscape($reader)) {
            return true;
        }

        return false;
    }

    public static function consumeIdent(Reader $reader): string
    {
        $result = '';

        while (true) {
            $char = $reader->peek();

            if ($char === null) {
                break;
            }

            if (CodePoint::isIdentChar($char)) {
                $result .= CodePoint::toCharacter($char);
                $reader->consume();
                continue;
            }

            if (self::isValidEscape($reader)) {
                // If so, we are escaping!
                // Append the escaped character.
                $result .= self::consumeEscape($reader);
                continue;
            }

            break;
        }

        return $result;
    }

    public static function wouldStartNumber(Reader $reader): bool
    {
        $first = $reader->peek();

        if ($first === Unicode::HYPHEN_MINUS || $first === Unicode::PLUS_SIGN) {
            $second = $reader->peek(1);

            if (CodePoint::isDigit($second)) {
                return true;
            }

            if ($second === Unicode::FULL_STOP) {
                return CodePoint::isDigit($reader->peek(2));
            }

            return false;
        }

        if ($first === Unicode::FULL_STOP) {
            return CodePoint::isDigit($reader->peek(1));
        }

        return CodePoint::isDigit($first);
    }

    public static function consumeAllDigits(Reader $reader): string
    {
        $value = '';

        while (true) {
            $char = $reader->peek();

            if (CodePoint::isDigit($char)) {
                $value .= CodePoint::toCharacter($char);

                $reader->consume();

                continue;
            }

            break;
        }

        return $value;
    }

    public static function consumeAllWhitespace(Reader $reader): string
    {
        $whitespace = '';

        while (! $reader->eof() && CodePoint::isWhitespace($char = $reader->peek())) {
            $whitespace .= CodePoint::toCharacter($char);
            $reader->consume();
        }

        return $whitespace;
    }

    public static function convertToNumber(string $number): float|int
    {
        if (
            str_contains($number, '.')
            || str_contains($number, 'e')
            || str_contains($number, 'E')
        ) {
            return (float)$number;
        }

        return (int)$number;
    }

    public static function opensDelimiterComment(Reader $reader): bool
    {
        if ($reader->peek() !== Unicode::LESS_THAN) {
            return false;
        }

        if ($reader->peek(1) !== Unicode::EXCLAMATION_MARK) {
            return false;
        }

        if ($reader->peek(2) !== Unicode::HYPHEN_MINUS) {
            return false;
        }

        if ($reader->peek(3) !== Unicode::HYPHEN_MINUS) {
            return false;
        }

        return true;
    }

    public static function closesDelimiterComment(Reader $reader): bool
    {
        if ($reader->peek() !== Unicode::HYPHEN_MINUS) {
            return false;
        }

        if ($reader->peek(1) !== Unicode::HYPHEN_MINUS) {
            return false;
        }

        if ($reader->peek(2) !== Unicode::GREATER_THAN) {
            return false;
        }

        return true;
    }
}

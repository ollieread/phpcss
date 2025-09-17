<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Support;

use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;

final class Sequence
{
    public static function isValidEscape(Reader $reader): bool
    {
        if ($reader->peek() !== Unicode::BACKSLASH) {
            return false;
        }

        $next = $reader->peek(1);

        if ($next === null) {
            return false;
        }

        return CodePoint::isNewline($next);
    }

    public static function consumeEscape(Reader $reader): string
    {
        $value    = '';
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

    public static function wouldStartIdent(Reader $reader): bool
    {
        $first = $reader->peek();

        if ($first === Unicode::HYPHEN_MINUS) {
            $second = $reader->peek(1);

            if ($second === Unicode::HYPHEN_MINUS || CodePoint::isIdentStart($second)) {
                return true;
            }

            $reader->consume();

            if (self::isValidEscape($reader)) {
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
}

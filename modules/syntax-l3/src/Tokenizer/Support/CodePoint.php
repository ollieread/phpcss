<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Support;

final readonly class CodePoint
{
    public static function is(int $a, int $b): bool
    {
        return $a === $b;
    }

    public static function isNot(int $a, int $b) : bool
    {
        return self::is($a, $b) === false;
    }

    /**
     * Convert a Unicode code point to a character.
     *
     * @param int $codePoint
     *
     * @return string
     */
    public static function toCharacter(int $codePoint): string
    {
        return mb_chr($codePoint, 'UTF-8');
    }

    /**
     * Get the integer value of a hex character.
     *
     * @param int $controlPoint
     *
     * @return int
     */
    public static function getHexValue(int $controlPoint): int
    {
        return $controlPoint <= Unicode::NINE
            ? $controlPoint - Unicode::ZERO
            : 10 + (($controlPoint | Unicode::SPACE) - Unicode::LOWER_A);
    }

    public static function isDigit(int $char): bool
    {
        return $char >= Unicode::ZERO && $char <= Unicode::NINE; // 0-9
    }

    public static function isHexDigit(int $char): bool
    {
        return self::isDigit($char) // 0-9
               || ($char >= Unicode::UPPER_A && $char <= Unicode::UPPER_F) // A-F
               || ($char >= Unicode::LOWER_A && $char <= Unicode::LOWER_F); // a-f
    }

    public static function isUppercase(int $char): bool
    {
        return $char >= Unicode::UPPER_A && $char <= Unicode::UPPER_Z; // A-Z
    }

    public static function isLowercase(int $char): bool
    {
        return $char >= Unicode::LOWER_A && $char <= Unicode::LOWER_Z; // a-z
    }

    public static function isLetter(int $char): bool
    {
        return self::isUppercase($char)
               || self::isLowercase($char);
    }

    public static function isNonAscii(int $char): bool
    {
        return $char >= Unicode::NUM_128;
    }

    public static function isIdentStart(int $char): bool
    {
        return self::isLetter($char)
               || self::isNonAscii($char)
               || $char === Unicode::UNDERSCORE; // _
    }

    public static function isIdentChar(int $char): bool
    {
        return self::isIdentStart($char)
               || self::isDigit($char)
               || $char === Unicode::HYPHEN; // -
    }

    public static function isNonPrintable(int $char): bool
    {
        return ($char >= Unicode::NULL && $char <= Unicode::BACKSPACE) // NULL-Backspace
               || $char === Unicode::TAB // Line Tabulation
               || ($char >= Unicode::SHIFT_OUT && $char <= Unicode::UNIT_SEPARATOR) // Shift Out-Unit Separator
               || $char === Unicode::DELETE; // Delete
    }

    public static function isNewline(int $char): bool
    {
        return $char === Unicode::LINE_FEED  // Line Feed
               || $char === Unicode::FORM_FEED  // Form Feed
               || $char === Unicode::CARRIAGE_RETURN; // Carriage Return
    }

    public static function isWhitespace(int $char): bool
    {
        return self::isNewline($char)
               || $char === Unicode::SPACE     // Space
               || $char === Unicode::TAB;  // Character Tabulation
    }

    public static function isSurrogate(int $char): bool
    {
        return ($char >= Unicode::SURROGATE_HIGH && $char <= Unicode::SURROGATE_HIGH_PRIVATE)
               || ($char >= Unicode::SURROGATE_LOW && $char <= Unicode::SURROGATE_LOW_END);
    }

    public static function isAsciiMatch(string $a, string $b): bool
    {
        $la = strlen($a);

        if ($la !== strlen($b)) {
            return false;
        }

        for ($i = 0; $i < $la; $i++) {
            $ca = ord($a[$i]);
            $cb = ord($b[$i]);

            if ($ca >= Unicode::UPPER_A && $ca <= Unicode::UPPER_Z) {
                $ca += Unicode::SPACE;
            } // A–Z → a–z

            if ($cb >= Unicode::UPPER_A && $cb <= Unicode::UPPER_Z) {
                $cb += Unicode::SPACE;
            }

            if ($ca !== $cb) {
                return false;
            }
        }

        return true;
    }
}

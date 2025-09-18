<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Support;

final readonly class Unicode
{
    const int FORWARD_SLASH = 0x2F; // /

    const int BACKSLASH = 0x5C; // \

    const int ASTERISK = 0x2A; // *

    const int ZERO = 0x30; // 0

    const int NINE = 0x39; // 9

    const int UPPER_A = 0x41; // A

    const int UPPER_E = 0x45; // E

    const int UPPER_F = 0x46; // F

    const int UPPER_Z = 0x5A; // Z

    const int LOWER_A = 0x61; // a

    const int LOWER_E = 0x65; // e

    const int LOWER_F = 0x66; // f

    const int LOWER_Z = 0x7A; // z

    const int NUM_128 = 0x80; // 128

    const int MAX = 0x10FFFF; // 1,114,111

    const int MIN = 0x0;

    const int UNDERSCORE = 0x5F; // _

    const int HYPHEN_MINUS = 0x2D; // -

    const int AT = 0x40; // @

    const int PLUS_SIGN = 0x2B; // +

    const int EXCLAMATION_MARK = 0x21; // !

    const int DOLLAR_SIGN = 0x24; // $

    const int PERCENT_SIGN = 0x25; // %

    const int AMPERSAND = 0x26; // &

    const int APOSTROPHE = 0x27; // '

    const int QUOTATION_MARK = 0x22; // "

    const int LEFT_PARENTHESIS = 0x28; // (

    const int RIGHT_PARENTHESIS = 0x29; // )

    const int LEFT_BRACKET = 0x5B; // [

    const int RIGHT_BRACKET = 0x5D; // ]

    const int LEFT_BRACE = 0x7B; // {

    const int RIGHT_BRACE = 0x7D; // }

    const int COMMA = 0x2C; // ,

    const int HYPHEN = 0x2D; // -

    const int NULL = 0x00; // NULL

    const int BACKSPACE = 0x08; // Backspace

    const int TAB = 0x09; // Line/Character Tabulation (Tab)

    const int SHIFT_OUT = 0x0E; // Shift Out

    const int UNIT_SEPARATOR = 0x1F; // Unit Separator

    const int DELETE = 0x7F; // Delete

    const int LINE_FEED = 0x0A; // Line Feed (New Line)

    const int FORM_FEED = 0x0C; // Form Feed

    const int CARRIAGE_RETURN = 0x0D; // Carriage Return

    const int SPACE = 0x20; // Space

    const int SURROGATE_HIGH = 0xD800; // High Surrogate Start

    const int SURROGATE_HIGH_PRIVATE = 0xDBFF; // High Surrogate End

    const int SURROGATE_LOW = 0xDC00; // Low Surrogate Start

    const int SURROGATE_LOW_END = 0xDFFF; // Low Surrogate End

    const int FULL_STOP = 0x2E; // .

    const int REPLACEMENT_CHARACTER = 0xFFFD; // �

    const int HASH = 0x23; // #

    const int LESS_THAN = 0x3C; // <

    const int GREATER_THAN = 0x3E; // >

    const int COLON = 0x3A; // :

    const int SEMICOLON = 0x3B; // ;
}

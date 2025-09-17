<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer;

/**
 * @see https://www.w3.org/TR/css-syntax-3/?utm_source=chatgpt.com#tokenization
 */
enum TokenType
{
    case Ident;

    case Function;

    case AtKeyword;

    case Hash;

    case String;

    case BadString;

    case Url;

    case BadUrl;

    case Delimiter;

    case Number;

    case Percentage;

    case Dimension;

    case Whitespace;

    case CDO;

    case CDC;

    case Colon;

    case Semicolon;

    case Comma;

    case OpenBracket;

    case CloseBracket;

    case OpenParenthesis;

    case CloseParenthesis;

    case OpenBrace;

    case CloseBrace;

    case EOF;

    case Comment;

    public function castValue(string $value): string|float
    {
        return match ($this) {
            self::Number, self::Percentage, self::Dimension => (float)$value,
            default                                         => $value,
        };
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer;

use ArrayIterator;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;
use Traversable;

final class Lexer implements IteratorAggregate
{
    public static function for(string $input): self
    {
        return new self(new Reader($input));
    }

    public static function from(string $filepath): self
    {
        if (! is_file($filepath)) {
            throw new InvalidArgumentException("File '$filepath' not found.");
        }

        $input = file_get_contents($filepath);

        if ($input === false) {
            throw new RuntimeException("Failed to read file '$filepath'.");
        }

        return self::for($input);
    }

    /**
     * @var \PhpCss\Modules\Syntax\L3\Tokenizer\Reader
     */
    private Reader $reader;

    /**
     * @var list<\PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer> List of consumers to use when tokenizing.
     */
    private array $consumers = [];

    /**
     * @var array<int, \PhpCss\Modules\Syntax\L3\Tokenizer\Token>
     */
    private array $tokens;

    /**
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable<int, \PhpCss\Modules\Syntax\L3\Tokenizer\Token>|\PhpCss\Modules\Syntax\L3\Tokenizer\Token[] An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @throws Exception on failure.
     */
    public function getIterator(): Traversable
    {
        if (! isset($this->tokens)) {
            $this->tokenize();
        }

        return new ArrayIterator($this->tokens);
    }

    private function tokenize(): void
    {
        $tokens = [];

        while ($this->reader->eof() === false) {
            $char     = $this->reader->peek();
            $position = $this->reader->position();

            // If it's null here, we've hit the end of the input.
            if ($char === null) {
                $tokens[]     = new Token(TokenType::EOF, '', $this->reader->position());
                $this->tokens = $tokens;

                return;
            }

            // Starts with a '/'?
            if ($char === 0x2F) {
                // Could be the start of a comment.
                if ($this->reader->peek(1) === 0x2A) {
                    // It's a comment /* ... */
                    $this->reader->consume(2);

                    $tokens[] = $this->consumeComment($position);
                    continue;
                }
            }

            // Whitespace?
            if ($this->isWhitespace($char)) {
                // Consume all whitespace until we run out.
                $tokens[] = $this->consumeWhitespace($position);
                continue;
            }

            // A " or ' ?
            if ($char === 0x22 || $char === 0x27) {
                // String starting with " or '.
                $tokens[] = $this->consumeString($position, $char);
                continue;
            }

            // A # ?
            if ($char === 0x23) {
                // Could be the start of a hash.
                // Is it the start of an ident, or a valid escape sequence?
                if (
                    $this->checkNextSequenceForValidEscape()
                    || $this->isIdentChar($this->reader->peek(1))
                ) {
                    // It is, so it's a hash we need to consume.
                    $tokens[] = $this->consumeHash($position);
                    continue;
                }

                // It is, so it's a delimiter.
                $tokens[] = $this->consumeDelimiter($position);
                continue;
            }

            // A ( ?
            if ($char === 0x28) {
                $tokens[] = new Token(TokenType::OpenParenthesis, '(', $position, 1);
                $this->reader->consume();
                continue;
            }

            // A ) ?
            if ($char === 0x29) {
                $tokens[] = new Token(TokenType::CloseParenthesis, ')', $position, 1);
                $this->reader->consume();
                continue;
            }

            // A + or - ? And a digit?
            if (($char === 0x2B || $char === 0x2D) && $this->isDigit($this->reader->peek(1))) {
                // Okay, so it's a number.

                // Consume the number.
                $tokens[] = $this->consumeNumber($position);
                continue;
            }

            // A + without a digit?
            if ($char === 0x2B) {
                // It's just a delimiter.
                $tokens[] = $this->consumeDelimiter($position);
                continue;
            }

            // A , ?
            if ($char === 0x2C) {
                // It's a comma.
                $tokens[] = new Token(TokenType::Comma, ',', $position, 1);
                $this->reader->consume();
            }

            // A - without a digit?
            if ($char === 0x2D) {
                if ($this->checkNextSequenceForCommentDelimiterClosing()) {
                    $tokens[] = $this->consumeCommentDelimiterClose($position);
                    continue;
                }

                if ($this->checkNextSequenceForStartOfIdent()) {
                    $tokens[] = $this->consumeIdentLike($position);
                    continue;
                }
            }
        }

        $this->tokens = $tokens;
    }

    /**
     * Handle a comment starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeComment(int $startPosition): Token
    {
        $comment = '/*';

        while (true) {
            $char = $this->reader->peek();

            if ($char === null) {
                // We hit an EOF before we finished the comment.
                throw new RuntimeException('Unexpected EOF in comment.');
            }

            if ($char === 0x2A && $this->reader->peek(1) === 0x2F) {
                // End of comment */
                $comment .= '*/';
                $this->reader->consume(2);

                break;
            }

            $comment .= $this->toChar($char);

            $this->reader->consume();
        }

        return new Token(
            TokenType::Comment,
            $comment,
            $startPosition,
            $this->reader->position() - $startPosition
        );
    }

    /**
     * Handle whitespace starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeWhitespace(int $startPosition): Token
    {
        $value = '';

        while (true) {
            $char = $this->reader->peek();

            if ($char === null || ! $this->isWhitespace($char)) {
                break;
            }

            $value .= $this->toChar($char);
            $this->reader->consume();
        }

        return new Token(
            TokenType::Whitespace,
            $value,
            $startPosition,
            $this->reader->position() - $startPosition
        );
    }

    /**
     * Handle a string starting at the given position.
     *
     * @param int $startPosition
     * @param int $openingChar
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeString(int $startPosition, int $openingChar): Token
    {
        $value = '';
        $type  = TokenType::String;

        $this->reader->consume();

        while (true) {
            $char = $this->reader->peek();

            if ($char === $openingChar) {
                // End of string
                $this->reader->consume();

                break;
            }

            // Is it a valid escape?
            if ($this->checkNextSequenceForValidEscape()) {
                // If so, we are escaping!
                // Eat the backslash as we don't need it.
                $this->reader->consume();

                // Append the escaped character.
                $this->appendEscaped($value);
            }

            // If it's a newline, it's a bad string.
            if ($this->isNewline($char)) {
                $type = TokenType::BadString;
                // Do not consume the newline, so it can be handled again.
                break;
            }

            if ($this->reader->eof()) {
                // EOF before the end of the string.
                // For some reason, we still return a string token.
                break;
            }
        }

        return new Token(
            $type,
            $value,
            $startPosition,
            $this->reader->position() - $startPosition
        );
    }

    /**
     * Handle a number starting at the given position.
     *
     * @param int      $startPosition
     * @param int|null $sign
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeNumber(int $startPosition): Token
    {
        $value = '';

        $start = $this->reader->peek();

        if ($start === 0x2B || $start === 0x2D) {
            $value .= $this->toChar($start);
            $this->reader->consume();
        }

        // Keep appending until we run out of digits.
        $this->appendWhileDigit($value);

        // Is there a decimal point?
        if (
            $this->reader->peek() === 0x2E
            && $this->isDigit($this->reader->peek(1))
        ) {
            // Decimal point followed by a digit.
            $value .= '.';

            // Consume the '.'.
            $this->reader->consume();

            // Keep appending until we run out of digits.
            $this->appendWhileDigit($value);
        } else {
            $char = $this->reader->peek();

            // Is it an exponent?
            if ($char === 0x45 || $char === 0x65) {
                // 'E' or 'e' notation.
                $value .= $this->toChar($char);
                $this->reader->consume();
                $next = $this->reader->peek();

                if ($next === 0x2D || $next === 0x2B) {
                    // + or - sign
                    $value .= $this->toChar($next);
                    $this->reader->consume();

                    $next = $this->reader->peek();
                }

                if ($this->isDigit($next)) {
                    // Keep appending until we run out of digits.
                    $this->appendWhileDigit($value);
                } else {
                    // Invalid exponent.
                    throw new RuntimeException('Invalid number, expected digit after exponent.');
                }
            }
        }

        return new Token(
            TokenType::Number,
            $value,
            $startPosition,
            mb_strlen($value)
        );
    }

    /**
     * Handle a hash starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeHash(int $startPosition): Token
    {
        if ($this->reader->peek() === 0x23) {
            // Consume the '#'.
            $this->reader->consume();
        }

        $value = '';
        $type  = 'unrestricted';

        if ($this->checkNextSequenceForStartOfIdent()) {
            $type = 'id';
        }

        $this->appendIdent($value);

        return new Token(
            TokenType::Hash,
            $value,
            $startPosition,
            $this->reader->position() - $startPosition,
            ['type' => $type]
        );
    }

    /**
     * Handle a delimiter starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeDelimiter(int $startPosition): Token
    {
        $char = $this->reader->peek();

        if ($char === null) {
            throw new RuntimeException('Unexpected EOF when trying to consume a delimiter.');
        }

        $this->reader->consume();

        return new Token(
            TokenType::Delimiter,
            $this->toChar($char),
            $startPosition,
            1
        );
    }

    /**
     * Handle a comment delimiter starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeCommentDelimiterOpen(int $startPosition): Token
    {
        $this->reader->consume(4);

        return new Token(
            TokenType::CDC,
            '<!--',
            $startPosition,
            4
        );
    }

    /**
     * Handle a comment delimiter closing starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeCommentDelimiterClose(int $startPosition): Token
    {
        $this->reader->consume(3);

        return new Token(
            TokenType::CDC,
            '-->',
            $startPosition,
            3
        );
    }

    /**
     * Handle an ident-like token starting at the given position.
     *
     * @param int $startPosition
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     */
    private function consumeIdentLike(int $startPosition): Token
    {
        $result = '';

        $this->appendIdent($result);

        if ($this->isAsciiMatch($result, 'url') && $this->reader->peek() === 0x28) {
            // It's a URl token.
            // Consume the '('.
            $this->reader->consume();

            // Consume while the next two characters are whitespace.
            while (
                $this->isWhitespace($this->reader->peek())
                && $this->isWhitespace($this->reader->peek(1))
                && ! $this->reader->eof()
            ) {
                $this->reader->consume();
            }

            $char = $this->reader->peek();

            // If it's whitespace, peek ahead slightly.
            if ($this->isWhitespace($char)) {
                $char = $this->reader->peek(1);
            }

            // If it's a " or ', it's a function.
            if ($char === 0x22 || $char === 0x27) {
                return new Token(
                    TokenType::Function,
                    $result,
                    $startPosition,
                    $this->reader->position() - $startPosition + 1 // +1 for the '('
                );
            }

            // If we're here, it's a URL.
            $type = TokenType::Url;

            // Consume all the whitespace.
            $this->consumeAllWhitespace();

            while (true) {
                $char = $this->reader->peek();

                if ($char === null) {
                    break;
                }

                if ($char === 0x29) {
                    // End of URL.
                    $this->reader->consume();
                    break;
                }

                if ($this->isWhitespace($char)) {
                    // Bad URL, consume all whitespace until we run out.
                    $this->consumeAllWhitespace();

                    $next = $this->reader->peek();

                    if ($next === null) {
                        break;
                    }

                    // End of URL.
                    if ($next === 0x29) {
                        $this->reader->consume();
                        break;
                    }

                    $type = TokenType::BadUrl;
                    continue;
                }

                if ($this->checkNextSequenceForValidEscape()) {
                    // If so, we are escaping!
                    // Eat the backslash as we don't need it.
                    $this->reader->consume();

                    // Append the escaped character.
                    $this->appendEscaped($result);
                    continue;
                }
            }
        }
    }

    /**
     * Append digits to the given value until we run out of digits.
     *
     * @param string $value
     *
     * @return void
     */
    private function appendWhileDigit(string &$value): void
    {
        while (true) {
            $char = $this->reader->peek();

            if ($this->isDigit($char)) {
                $value .= $this->toChar($char);
                $this->reader->consume();

                continue;
            }

            break;
        }
    }

    /**
     * Append an escaped character to the given value.
     *
     * @param string $value
     *
     * @return void
     */
    private function appendEscaped(string &$value): void
    {
        $nextChar = $this->reader->peek();

        if ($nextChar === null) {
            $this->reader->consume();
            $value .= $this->replacementCharacter();
            return;
        }

        if ($this->isHexDigit($nextChar)) {
            $totalEscaped = 0;
            $escapedValue = 0;

            while ($totalEscaped < 6 && $this->isHexDigit($this->reader->peek())) {
                $escapedValue = ($escapedValue << 4) | $this->getHexValue($this->reader->peek());
                $this->reader->consume();
                $totalEscaped++;
            }

            if ($this->isWhitespace($this->reader->peek())) {
                // Consume the whitespace.
                $this->reader->consume();
            }

            if ($escapedValue === 0 || $escapedValue > 0x10FFFF || $this->isSurrogate($escapedValue)) {
                $value .= $this->replacementCharacter();

                return;
            }

            $value .= $this->toChar($escapedValue);

            return;
        }

        $value .= $this->toChar($nextChar);
    }

    /**
     * Append an ident to the given value.
     *
     * @param string $value
     *
     * @return void
     */
    private function appendIdent(string &$value): void
    {
        $result = '';

        while (true) {
            $char = $this->reader->peek();

            if ($char === null) {
                break;
            }

            if ($this->isIdentChar($char)) {
                $result .= $this->toChar($char);
                $this->reader->consume();
                continue;
            }

            if ($this->checkNextSequenceForValidEscape()) {
                // If so, we are escaping!
                // Eat the backslash as we don't need it.
                $this->reader->consume();

                // Append the escaped character.
                $this->appendEscaped($result);
                continue;
            }

            break;
        }

        $value .= $result;
    }

    /**
     * Consume all whitespace characters until we run out.
     *
     * @return void
     */
    private function consumeAllWhitespace(): void
    {
        while ($this->isWhitespace($this->reader->peek())) {
            $this->reader->consume();
        }
    }

    /**
     * @param array{int, int} $codePoints
     *
     * @return bool
     */
    private function checkValidEscape(array $codePoints): bool
    {
        return ! ($codePoints[0] !== 0x5C
                  || $codePoints[1] === null
                  || $this->isNewline($codePoints[1]));
    }

    private function checkNextSequenceForValidEscape(): bool
    {
        return $this->checkValidEscape([
            $this->reader->peek(),
            $this->reader->peek(1),
        ]);
    }

    /**
     * @param array{int, int, int} $codePoints
     *
     * @return bool
     */
    private function checkWouldStartIdent(array $codePoints): bool
    {
        if ($codePoints[0] === 0x2D) { // -
            return $this->isIdentStart($codePoints[1])
                   || $codePoints[1] === 0x2D // -
                   || $this->checkValidEscape([$codePoints[1], $codePoints[2]]);
        }

        if ($this->isIdentStart($codePoints[0])) {
            return true;
        }

        if ($codePoints[0] === 0x5C) { // \
            return $this->checkValidEscape([$codePoints[0], $codePoints[1]]);
        }

        return false;
    }

    private function checkNextSequenceForStartOfIdent(): bool
    {
        return $this->checkWouldStartIdent([
            $this->reader->peek(),
            $this->reader->peek(1),
            $this->reader->peek(2),
        ]);
    }

    /**
     * @param array{int, int, int} $codePoints
     *
     * @return bool
     */
    private function checkCommentDelimiterClose(array $codePoints): bool
    {
        return $codePoints[0] === 0x2D // -
               && $codePoints[1] === 0x2D // -
               && $codePoints[2] === 0x3E; // >
    }

    private function checkNextSequenceForCommentDelimiterClosing(): bool
    {
        return $this->checkCommentDelimiterClose([
            $this->reader->peek(),
            $this->reader->peek(1),
            $this->reader->peek(2),
        ]);
    }

    /**
     * @param array{int, int, int, int} $codePoints
     *
     * @return bool
     */
    private function checkCommentDelimiterOpen(array $codePoints): bool
    {
        return $codePoints[0] === 0x3C // <
               && $codePoints[1] === 0x21 // !
               && $codePoints[2] === 0x2D // -
               && $codePoints[3] === 0x2D; // -
    }

    private function checkNextSequenceForCommentDelimiterOpening(): bool
    {
        return $this->checkCommentDelimiterClose([
            $this->reader->peek(),
            $this->reader->peek(1),
            $this->reader->peek(2),
            $this->reader->peek(3),
        ]);
    }

    private function toChar(int $char): string
    {
        return mb_chr($char, 'UTF-8');
    }

    private function replacementCharacter(): string
    {
        return $this->toChar(0xFFFD);
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\BraceConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class BraceConsumerTest extends BaseConsumerTest
{
    protected function createConsumer(): Consumer
    {
        return new BraceConsumer();
    }

    /**
     * @return array<string, array{
     *     string,
     *     \PhpCss\Modules\Syntax\L3\Tokenizer\TokenType,
     *     string|int|float|null,
     *     int,
     *     int
     * }>
     */
    public static function canConsumeDataProvider(): array
    {
        return [
            'Opening brace ({)' => ['{ margin: 0; }', TokenType::OpenBrace, '{', 0, 1,],
            'Closing brace (})' => ['} .some-test { }', TokenType::CloseBrace, '}', 0, 1,],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'No brace'        => ['.some-test { }'],
            'Only whitespace' => [" \t\r\n"],
            'Empty string'    => [''],
            'Other character' => ['; .color: black;'],
            'Parenthesis'     => ['()'],
            'Bracket'         => ['[attribute=value]'],
        ];
    }

    /**
     * @return array<string, array{string, string, array{
     *      string,
     *      \PhpCss\Modules\Syntax\L3\Tokenizer\TokenType,
     *      string|int|float|null,
     *      int,
     *      int
     *  }|null
     * }>
     */
    public static function throwsParseErrorDataProvider(): array
    {
        return [
            'Consumer but without a brace' => ['.some-test { }', 'Tried to consume a brace, but no brace found.', true, null],
        ];
    }
}

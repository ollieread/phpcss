<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\BracketConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class BracketConsumerTest extends BaseConsumerTest
{
    protected function createConsumer(): Consumer
    {
        return new BracketConsumer();
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
            'Opening bracket ([)' => ['[attribute=value]', TokenType::OpenBracket, '[', 0, 1,],
            'Closing bracket (])' => ['].some-test { }', TokenType::CloseBracket, ']', 0, 1,],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'No bracket'      => ['.some-test { }'],
            'Only whitespace' => [" \t\r\n"],
            'Empty string'    => [''],
            'Other character' => ['; .color: black;'],
            'Parenthesis'     => ['()'],
            'Brace'           => ['{ margin: 0; }'],
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
            'Consumer but without a bracket' => ['.some-test { }', 'Tried to consume a bracket, but no bracket found.', true, null],
        ];
    }
}

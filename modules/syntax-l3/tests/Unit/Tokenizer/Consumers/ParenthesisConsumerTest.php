<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\ParenthesisConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class ParenthesisConsumerTest extends BaseConsumerTest
{
    protected function createConsumer(): Consumer
    {
        return new ParenthesisConsumer();
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
            'Opening parenthesis ([)' => ['()', TokenType::OpenParenthesis, '(', 0, 1,],
            'Closing parenthesis (])' => ['); margin: 0 auto;', TokenType::CloseParenthesis, ')', 0, 1,],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'No parenthesis'  => ['.some-test { }'],
            'Only whitespace' => [" \t\r\n"],
            'Empty string'    => [''],
            'Other character' => ['; .color: black;'],
            'Brace'           => ['{ margin: 0; }'],
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
            'Consumer but without a parenthesis' => ['.some-test { }', 'Tried to consume a parenthesis, but no parenthesis found.', true, null],
        ];
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\CommaConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class CommaConsumerTest extends BaseConsumerTest
{

    protected function createConsumer(): Consumer
    {
        return new CommaConsumer();
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
            'simple comma'              => [
                ',',
                TokenType::Comma,
                ',',
                0,
                1,
            ],
            'comma followed by space'   => [
                ', ',
                TokenType::Comma,
                ',',
                0,
                1,
            ],
            'comma followed by word'    => [
                ',word',
                TokenType::Comma,
                ',',
                0,
                1,
            ],
            'comma followed by number'  => [
                ',123',
                TokenType::Comma,
                ',',
                0,
                1,
            ],
            'comma followed by a colon' => [
                ',:',
                TokenType::Comma,
                ',',
                0,
                1,
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'empty string'     => [''],
            'space'            => [' '],
            'word'             => ['word'],
            'number'           => ['123'],
            'semicolon'        => [';'],
            'colon'            => [':'],
            'dot'              => ['.'],
            'hash'             => ['#'],
            'plus'             => ['+'],
            'greater than'     => ['>'],
            'tilde'            => ['~'],
            'asterisk'         => ['*'],
            'equals'           => ['='],
            'whitespace start' => [' ,word'],
        ];
    }
}

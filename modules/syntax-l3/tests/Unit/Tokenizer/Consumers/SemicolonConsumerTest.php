<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\SemicolonConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class SemicolonConsumerTest extends BaseConsumerTest
{

    protected function createConsumer(): Consumer
    {
        return new SemicolonConsumer();
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
            'simple semicolon'              => [
                ';',
                TokenType::Semicolon,
                ';',
                0,
                1,
            ],
            'semicolon followed by space'   => [
                '; ',
                TokenType::Semicolon,
                ';',
                0,
                1,
            ],
            'semicolon followed by word'    => [
                ';word',
                TokenType::Semicolon,
                ';',
                0,
                1,
            ],
            'semicolon followed by number'  => [
                ';123',
                TokenType::Semicolon,
                ';',
                0,
                1,
            ],
            'semicolon followed by a colon' => [
                ';:',
                TokenType::Semicolon,
                ';',
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
            'comma'            => [','],
            'colon'            => [':'],
            'dot'              => ['.'],
            'hash'             => ['#'],
            'plus'             => ['+'],
            'greater than'     => ['>'],
            'tilde'            => ['~'],
            'asterisk'         => ['*'],
            'equals'           => ['='],
            'whitespace start' => [' ;word'],
        ];
    }
}

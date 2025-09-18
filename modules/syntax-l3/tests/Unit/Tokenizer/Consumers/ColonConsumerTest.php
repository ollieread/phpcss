<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\ColonConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class ColonConsumerTest extends BaseConsumerTest
{

    protected function createConsumer(): Consumer
    {
        return new ColonConsumer();
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
            'simple colon'                    => [
                ':',
                TokenType::Colon,
                ':',
                0,
                1,
            ],
            'colon followed by space'         => [
                ': ',
                TokenType::Colon,
                ':',
                0,
                1,
            ],
            'colon followed by word'          => [
                ':word',
                TokenType::Colon,
                ':',
                0,
                1,
            ],
            'colon followed by number'        => [
                ':123',
                TokenType::Colon,
                ':',
                0,
                1,
            ],
            'colon followed by another colon' => [
                '::',
                TokenType::Colon,
                ':',
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
            'empty string'       => [''],
            'space'              => [' '],
            'word'               => ['word'],
            'number'             => ['123'],
            'comma'              => [','],
            'semicolon'          => [';'],
            'dot'                => ['.'],
            'hash'               => ['#'],
            'plus'               => ['+'],
            'greater than'       => ['>'],
            'tilde'              => ['~'],
            'asterisk'           => ['*'],
            'equals'             => ['='],
            'whitespace start' => [' ::word'],
        ];
    }
}

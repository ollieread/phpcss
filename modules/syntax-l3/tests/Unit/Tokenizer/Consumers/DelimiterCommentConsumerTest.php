<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\DelimiterCommentConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class DelimiterCommentConsumerTest extends BaseConsumerTest
{

    protected function createConsumer(): Consumer
    {
        return new DelimiterCommentConsumer();
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
            'simple opening delimiter comment'              => [
                '<!--',
                TokenType::CDO,
                '<!--',
                0,
                4,
            ],
            'simple delimiter comment with content'         => [
                '<!-- this is a comment -->' . PHP_EOL . 'body { color: red; }',
                TokenType::CDO,
                '<!--',
                0,
                4,
            ],
            'simple closing delimiter comment'              => [
                '-->',
                TokenType::CDC,
                '-->',
                0,
                3,
            ],
            'simple closing delimiter comment with content' => [
                '-->' . PHP_EOL . 'body { color: red; }',
                TokenType::CDC,
                '-->',
                0,
                3,
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'not a comment' => ['body { color: red; }'],
            'empty string'  => [''],
            'whitespace'    => [" \t\r\n"],
            'partial CDO'   => ['<!-'],
            'partial CDC'   => ['--'],
        ];
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures\BaseConsumerTest;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\CommentConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class CommentConsumerTest extends BaseConsumerTest
{
    protected function createConsumer(): Consumer
    {
        return new CommentConsumer();
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
            'simple comment'       => [
                '/* simple comment */',
                TokenType::Comment,
                ' simple comment ',
                0,
                20,
            ],
            'comment with stars'   => [
                '/** comment **/',
                TokenType::Comment,
                '* comment *',
                0,
                15,
            ],
            'comment with slashes' => [
                '/* // comment // */',
                TokenType::Comment,
                ' // comment // ',
                0,
                19,
            ],
            'empty comment'        => [
                '/**/',
                TokenType::Comment,
                '',
                0,
                4,
            ],
            'comment with newline' => [
                '/* comment \n on multiple \n lines */',
                TokenType::Comment,
                ' comment \n on multiple \n lines ',
                0,
                37,
            ],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function canNotConsumeDataProvider(): array
    {
        return [
            'no comment'          => ['no comment'],
            'just slashes'        => ['/////'],
            'not a comment start' => [' / not a comment */'],
        ];
    }

    /**
     * @return array<string, array{string, string, bool, array{
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
            'unclosed comment' => [
                '/* unclosed comment',
                'EOF encountered before comment was closed.',
                true,
                null,
            ],
            'just open'        => [
                '/*',
                'EOF encountered before comment was closed.',
                true,
                null,
            ],
            'empty input'      => [
                '',
                'EOF encountered before comment was closed.',
                true,
                null,
            ],
        ];
    }
}

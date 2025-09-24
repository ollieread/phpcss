<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Sequence;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class DelimiterCommentConsumer implements Consumer
{
    /**
     * Consume characters from the reader and return a token.
     *
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     *
     * @return \PhpCss\Modules\Syntax\L3\Tokenizer\Token
     *
     * @throws \PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException
     */
    public function consume(Reader $reader): Token
    {
        $position = $reader->start();

        if (Sequence::opensDelimiterComment($reader)) {
            $value = '<!--';
            $type  = TokenType::CDO;
            $reader->consume(4);
        } else {
            $value = '-->';
            $type  = TokenType::CDC;
            $reader->consume(3);
        }

        return new Token(
            $type,
            $value,
            $position,
            $reader->finish()
        );
    }

    /**
     * Check if this consumer can consume from the current reader position.
     *
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader $reader
     *
     * @return bool
     */
    public function canConsume(Reader $reader): bool
    {
        return Sequence::opensDelimiterComment($reader)
               || Sequence::closesDelimiterComment($reader);
    }
}

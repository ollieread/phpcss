<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer\Consumers;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\CodePoint;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Sequence;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Unicode;
use PhpCss\Modules\Syntax\L3\Tokenizer\Token;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;

class HashConsumer implements Consumer
{
    const string ID_TYPE = 'id';

    const string UNRESTRICTED_TYPE = 'unrestricted';

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

        // If the hash is still present, we need to eat it so it doesn't get in
        // the way.
        if ($reader->peek() === Unicode::HASH) {
            $reader->consume();
        }

        $type = self::UNRESTRICTED_TYPE;

        if (Sequence::wouldStartIdent($reader)) {
            $type = self::ID_TYPE;
        }

        return new Token(
            TokenType::Hash,
            Sequence::consumeIdent($reader),
            $position,
            $reader->finish(),
            ['type' => $type]
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
        return $reader->peek() === Unicode::HASH
               && (
                   Sequence::isValidEscape($reader, 1)
                   || CodePoint::isIdentChar($reader->peek(1))
               );
    }
}

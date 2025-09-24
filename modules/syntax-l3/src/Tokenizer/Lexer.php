<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer;

use ArrayIterator;
use Exception;
use InvalidArgumentException;
use IteratorAggregate;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\BraceConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\BracketConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\ColonConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\CommaConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\CommentConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\DelimiterCommentConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\DelimiterConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\EOFConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\HashConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\IdentLikeConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\NumericConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\ParenthesisConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\SemicolonConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\StringConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Consumers\WhitespaceConsumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException;
use PhpCss\Modules\Syntax\L3\Tokenizer\Support\Unicode;
use RuntimeException;
use Traversable;

final class Lexer implements IteratorAggregate
{
    public static function for(string $input): self
    {
        return new self(new Reader($input), [
            new BraceConsumer(),
            new BracketConsumer(),
            new ColonConsumer(),
            new CommaConsumer(),
            new CommentConsumer(),
            new DelimiterCommentConsumer(),
            $default = new DelimiterConsumer(),
            new EOFConsumer(),
            new HashConsumer(),
            new IdentLikeConsumer(),
            new NumericConsumer(),
            new ParenthesisConsumer(),
            new SemicolonConsumer(),
            new StringConsumer(),
            new WhitespaceConsumer(),
        ], $default);
    }

    public static function from(string $filepath): self
    {
        if (! is_file($filepath)) {
            throw new InvalidArgumentException("File '$filepath' not found.");
        }

        $input = file_get_contents($filepath);

        if ($input === false) {
            throw new RuntimeException("Failed to read file '$filepath'.");
        }

        return self::for($input);
    }

    /**
     * @var \PhpCss\Modules\Syntax\L3\Tokenizer\Reader
     */
    private Reader $reader;

    /**
     * @var list<\PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer> List of consumers to use when tokenizing.
     */
    private array $consumers;

    /**
     * @var \PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer The default consumer to use when no other consumer can handle the input.
     */
    private Consumer $defaultConsumer;

    /**
     * @var array<int, \PhpCss\Modules\Syntax\L3\Tokenizer\Token>
     */
    private array $tokens;

    /**
     * @var array<int, string> List of errors encountered during tokenization.
     */
    private array $errors;

    /**
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Reader                         $reader
     * @param array<int, \PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer> $consumers
     * @param \PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer             $defaultConsumer
     */
    public function __construct(Reader $reader, array $consumers, Consumer $defaultConsumer)
    {
        $this->reader          = $reader;
        $this->consumers       = $consumers;
        $this->defaultConsumer = $defaultConsumer;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable<int, \PhpCss\Modules\Syntax\L3\Tokenizer\Token>|\PhpCss\Modules\Syntax\L3\Tokenizer\Token[] An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @throws Exception on failure.
     */
    public function getIterator(): Traversable
    {
        if (! isset($this->tokens)) {
            $this->tokenize();
        }

        return new ArrayIterator($this->tokens);
    }

    private function tokenize(): void
    {
        $tokens = [];
        $errors = [];

        while ($this->reader->eof() === false) {
            foreach ($this->consumers as $consumer) {
                if ($consumer->canConsume($this->reader)) {
                    $this->callConsumer($consumer, $tokens, $errors);

                    continue 2;
                }

                // If it's a backslash, it's an invalid escape sequence.
                if ($this->reader->peek() === Unicode::BACKSLASH) {
                    // Invalid escape sequence.
                    $errors[] = [
                        'offset'  => $this->reader->position(),
                        'length'  => 1,
                        'message' => 'Invalid escape sequence.',
                    ];
                }

                // Call the default consumer.
                $this->callConsumer($this->defaultConsumer, $tokens, $errors);

                continue 2;
            }
        }

        $this->tokens = $tokens;
        $this->errors = $errors;
    }

    private function callConsumer(Consumer $consumer, array &$tokens, array &$errors): void
    {
        // Track the start position in case of an error.
        $start = $this->reader->position();

        try {
            $tokens[] = $consumer->consume($this->reader);
        } catch (ParseErrorException $exception) {
            // There was a parse error, though sometimes that still
            // means there's a token to return.
            $errorToken = null;

            if ($exception->hasToken()) {
                $errorToken = $tokens[] = $exception->getToken();
            }

            $errors[] = [
                'offset'  => $errorToken?->offset ?? $start,
                'length'  => $errorToken?->length ?? ($this->reader->position() - $start),
                'message' => $exception->getMessage(),
            ];
        }
    }
}

<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tokenizer;

/**
 *
 */
final class Reader
{
    /**
     * @var string
     */
    private string $input;

    /**
     * @var int
     */
    private int $offset = 0;

    /**
     * @var int
     */
    private int $length;

    /**
     * @var list<int>
     */
    private array $checkpoints = [];

    private int $start = 0;

    /**
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input  = $input;
        $this->length = mb_strlen($input);
    }

    /**
     * Check if it's the end of the file.
     *
     * @return bool
     */
    public function eof(): bool
    {
        return $this->offset >= $this->length;
    }

    public function get(int $offset, $length = 1): ?int
    {
        $substr = mb_substr($this->input, $offset, $length);

        if (! empty($substr)) {
            return mb_ord($substr, 'UTF-8');
        }

        return null;
    }

    /**
     * Peek at the next character without advancing the offset.
     *
     * @param int $length
     *
     * @return int|null
     */
    public function peek(int $length = 0): ?int
    {
        // If it's the end of the file, return null.
        if ($this->eof()) {
            return null;
        }

        return $this->get($this->offset + $length);
    }

    /**
     * Get the next character and advance the offset.
     *
     * @return int|null
     */
    public function next(): ?int
    {
        // If it's the end of the file, return null.
        if ($this->eof()) {
            return null;
        }

        return $this->get($this->offset++);
    }

    /**
     * Consume a number of characters, advancing the offset.
     *
     * @param int $length
     *
     * @return void
     */
    public function consume(int $length = 1): void
    {
        for ($i = 0; $i < $length; $i++) {
            $this->next();
        }
    }

    /**
     * Move the offset back by one character.
     *
     * @return void
     */
    public function back(): void
    {
        if ($this->offset > 0) {
            $this->offset--;
        }
    }

    /**
     * Mark the current position for potential backtracking.
     *
     * @return void
     */
    public function mark(): void
    {
        $this->checkpoints[] = $this->offset;
    }

    /**
     * Revert to the last marked position.
     *
     * @return void
     */
    public function revert(): void
    {
        $checkpoint = array_pop($this->checkpoints);

        if ($checkpoint !== null) {
            $this->offset = $checkpoint;
        }
    }

    /**
     * Discard the last marked position.
     *
     * @return void
     */
    public function flush(): void
    {
        array_pop($this->checkpoints);
    }

    /**
     * Get the current position.
     *
     * @return int
     */
    public function position(): int
    {
        return $this->offset;
    }

    public function start(): int
    {
        return $this->start = $this->offset;
    }

    public function finish(): int
    {
        $diff = $this->offset - $this->start;

        $this->start = $this->offset;

        return $diff;
    }
}

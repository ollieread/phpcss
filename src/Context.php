<?php
declare(strict_types=1);

namespace PhpCSS;

final readonly class Context
{
    /**
     * @var list<string>
     */
    private array $stack;

    public function __construct(
        array $stack = []
    )
    {
        $this->stack = $stack;
    }

    public function stack(): array
    {
        return $this->stack;
    }

    public function isInside(string $name): bool
    {
        return array_any($this->stack, static fn ($item) => strtolower($item) === strtolower($name));
    }
}

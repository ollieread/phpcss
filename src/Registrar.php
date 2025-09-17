<?php
declare(strict_types=1);

namespace PhpCSS;

use PhpCSS\Contracts\PreludeHandler;

/**
 * @phpstan-type PreludeGuard = callable(Context): bool
 */
final class Registrar
{
    /**
     * @var array<int, array{callable(Context): bool, PreludeHandler}>
     * @phpstan-var array<int, array{PreludeGuard, PreludeHandler}>
     */
    private array $qualifiedPreludeHandlers = [];

    /**
     * @param callable(): bool                 $guard
     * @param \PhpCSS\Contracts\PreludeHandler $handler
     * @param int                              $priority
     *
     * @phpstan-param PreludeGuard             $guard
     *
     * @return self
     */
    public function onQualifiedPrelude(callable $guard, PreludeHandler $handler, int $priority = 0): self
    {
        // Register the handler with its guard.
        $this->qualifiedPreludeHandlers[$priority] = [$guard, $handler];

        // Make sure the handlers are sorted by priority.
        krsort($this->qualifiedPreludeHandlers);

        return $this;
    }
}

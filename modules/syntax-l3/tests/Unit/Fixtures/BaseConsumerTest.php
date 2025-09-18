<?php
declare(strict_types=1);

namespace PhpCss\Modules\Syntax\L3\Tests\Unit\Fixtures;

use PhpCss\Modules\Syntax\L3\Tokenizer\Contracts\Consumer;
use PhpCss\Modules\Syntax\L3\Tokenizer\Exceptions\ParseErrorException;
use PhpCss\Modules\Syntax\L3\Tokenizer\Reader;
use PhpCss\Modules\Syntax\L3\Tokenizer\TokenType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

abstract class BaseConsumerTest extends TestCase
{
    private Consumer $consumer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consumer = $this->createConsumer();
    }

    #[DataProvider('canConsumeDataProvider')]
    #[Test]
    public function canConsume(string $input, TokenType $type, string|int|float|null $value, int $offset, int $length): void
    {
        $reader = new Reader($input);

        $this->assertTrue($this->consumer->canConsume($reader));

        $token = $this->consumer->consume($reader);

        $this->assertSame($type, $token->type);
        $this->assertSame($value, $token->value);
        $this->assertSame($offset, $token->offset);
        $this->assertSame($length, $token->length);
    }

    #[DataProvider('canNotConsumeDataProvider')]
    #[Test]
    public function canNotConsume(string $input, ?array $expectedTokenData = null): void
    {
        $reader = new Reader($input);

        $this->assertFalse($this->consumer->canConsume($reader));

        if ($expectedTokenData !== null) {
            $token = $this->consumer->consume($reader);

            [$type, $value, $offset, $length] = $expectedTokenData;

            $this->assertSame($type, $token->type);
            $this->assertSame($value, $token->value);
            $this->assertSame($offset, $token->offset);
            $this->assertSame($length, $token->length);
        }
    }

    #[DataProvider('throwsParseErrorDataProvider')]
    #[Test]
    public function throwsParseError(?string $input, ?string $message, bool $skipCheck = false, ?array $expectedTokenData = null): void
    {
        if ($input === null) {
            $this->markTestSkipped('Consumer does not have any parse error tests.');
        }

        $reader = new Reader($input);

        if ($skipCheck === false) {
            $this->assertTrue($this->consumer->canConsume($reader));
        }

        $this->expectException(ParseErrorException::class);
        $this->expectExceptionMessage($message);

        $token = $this->consumer->consume($reader);

        if ($expectedTokenData !== null && $token !== null) {
            [$type, $value, $offset, $length] = $expectedTokenData;

            $this->assertSame($type, $token->type);
            $this->assertSame($value, $token->value);
            $this->assertSame($offset, $token->offset);
            $this->assertSame($length, $token->length);
        }
    }

    abstract protected function createConsumer(): Consumer;

    /**
     * @return array<string, array{
     *     string,
     *     \PhpCss\Modules\Syntax\L3\Tokenizer\TokenType,
     *     string|int|float|null,
     *     int,
     *     int
     * }>
     */
    abstract public static function canConsumeDataProvider(): array;

    /**
     * @return array<string, array{string}>
     */
    abstract public static function canNotConsumeDataProvider(): array;

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
            'No parse error tests' => [null, null, false, null],
        ];
    }
}

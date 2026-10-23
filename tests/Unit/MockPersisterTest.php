<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock\Tests;

use PHPUnit\Framework\TestCase;
use TeamBlue\ClientMock\MockMethod;
use TeamBlue\ClientMock\MockPersister;
use TeamBlue\ClientMock\Tests\Unit\MockPersisterData\TestImmutable;
use TeamBlue\ClientMock\Tests\Unit\MockPersisterData\TestReturnValueTransformer;

use function array_shift;
use function assert;

class MockPersisterTest extends TestCase
{
    private MockPersister $mockPersister;

    protected function setUp(): void
    {
        $this->mockPersister = new MockPersister(new TestReturnValueTransformer());
    }

    public function testInvoke(): void
    {
        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test']));

        $calls = $this->mockPersister->calls();
        $this->assertCount(1, $calls);

        $call = array_shift($calls);
        assert($call instanceof MockMethod);
        $this->assertEquals('foo', $call->method());
        $this->assertCount(1, $call->parametersPerCall());
    }

    public function testDoubleInvoke(): void
    {
        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test']));

        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test2']));

        $calls = $this->mockPersister->calls();
        $call = array_shift($calls);
        assert($call instanceof MockMethod);
        $this->assertCount(2, $call->parametersPerCall());
    }

    public function testInvokeWithoutReturn(): void
    {
        $this->expectExceptionMessageMatches('/You must call withReturnValue\(\) after calling a method\./');
        ($this->mockPersister)('foo', 'bar', 'baz');
        ($this->mockPersister)('foo', 'bar', 'baz');
    }

    public function testRemoveMockMethodsExcept(): void
    {
        ($this->mockPersister)('foo', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test']));

        ($this->mockPersister)('foo2', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test2']));

        ($this->mockPersister)('foo3', 'bar', 'baz')
            ->withReturnValue(TestImmutable::fromArray(['test' => 'test2']));

        $this->mockPersister->removeMockMethodsExcept('foo', 'foo2');

        $calls = $this->mockPersister->calls();

        $this->assertCount(2, $calls);
        $this->assertArrayHasKey('foo', $calls);
        $this->assertArrayHasKey('foo2', $calls);
    }
}

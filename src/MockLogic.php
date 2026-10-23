<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock;

use Closure;
use EventEngine\Data\ImmutableRecord;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Stringable;
use Throwable;

use function array_map;
use function array_slice;
use function count;
use function get_debug_type;
use function is_iterable;
use function is_scalar;
use function json_encode;
use function sprintf;

trait MockLogic
{
    private MockPersister $persister;

    public function setPersister(MockPersister $persister): self
    {
        $this->persister = $persister;

        return $this;
    }

    /** @return array<string, MockMethod> */
    public function calls(): array
    {
        return $this->persister->calls();
    }

    public function withReturnValue(mixed $returnValue): self
    {
        $this->persister->withReturnValue($returnValue);

        return $this;
    }

    public function withException(Throwable|Closure $exception): self
    {
        $this->persister->withException($exception);

        return $this;
    }

    public function repeat(int $times = 1): self
    {
        $this->persister->repeat($times);

        return $this;
    }

    public function removeMockMethods(string ...$methods): self
    {
        $this->persister->removeMockMethods(...$methods);

        return $this;
    }

    public function removeMockMethodsExcept(string ...$methods): self
    {
        $this->persister->removeMockMethodsExcept(...$methods);

        return $this;
    }

    public function removeAllMocks(): self
    {
        $this->persister->removeAllMocks();

        return $this;
    }

    public function removeMockMethodForIndex(string $method, int $index): self
    {
        $this->persister->removeMockMethodForIndex($method, $index);

        return $this;
    }

    public function build(TestCase $testCase, MockObject $mock): void
    {
        foreach ($this->calls() as $call) {
            $parametersPerCall = $call->parametersPerCall();
            $returnValuesOrExceptions = $call->returnValuesOrExceptions();

            $matcher = new InvokedCount(count($parametersPerCall));
            $mock
                ->expects($matcher)
                ->method($call->method())
                ->willReturnCallback(
                    function (...$actualParameters) use (
                        $testCase,
                        $matcher,
                        $parametersPerCall,
                        $returnValuesOrExceptions,
                        $call,
                    ) {
                        $invocation = $matcher->numberOfInvocations();
                        $expectedParameters = $parametersPerCall[$invocation - 1];
                        $returnOrException = $returnValuesOrExceptions[$invocation - 1];

                        if (count($actualParameters) > count($expectedParameters)) {
                            $actualParameters = array_slice($actualParameters, 0, count($expectedParameters));
                        }

                        foreach ($expectedParameters as $index => $expectedParameter) {
                            $actualParameter = $actualParameters[$index];
                            if ($expectedParameter instanceof Closure) {
                                $expectedParameter($actualParameter);
                                continue;
                            }

                            $testCase->assertEquals(
                                $expectedParameter,
                                $actualParameter,
                                sprintf(
                                    'Parameter %d for method %s does not match on invocation %d ' .
                                    '=> expectedParameter: %s, actualParameter: %s',
                                    $index + 1,
                                    $call->method(),
                                    $invocation,
                                    $this->parameterToString($expectedParameter),
                                    $this->parameterToString($actualParameter),
                                ),
                            );
                        }

                        if ($returnOrException['type'] === 'exception') {
                            /** @var Throwable|Closure $exception */
                            $exception = $returnOrException['value'];

                            if ($exception instanceof Closure) {
                                $exception = $exception($testCase);
                            }

                            throw $exception;
                        }

                        return $this->transformReturnValueOnBuild($returnOrException['value'], $testCase);
                    },
                );
        }

        $this->persister->removeAllMocks();
    }

    private function transformReturnValueOnBuild(mixed $value, TestCase $testCase): mixed
    {
        return $value;
    }

    private function parameterToString(mixed $parameter): string
    {
        if ($parameter === null) {
            return 'null';
        }

        if (is_iterable($parameter)) {
            return json_encode(array_map(fn ($item) => $this->parameterToString($item), (array) $parameter)) ?: '[]';
        }

        if (is_scalar($parameter) || $parameter instanceof Stringable) {
            return (string) $parameter;
        }

        if ($parameter instanceof ImmutableRecord) {
            return $this->parameterToString($parameter->toArray());
        }

        throw new InvalidArgumentException('Unsupported parameter type: ' . get_debug_type($parameter));
    }
}

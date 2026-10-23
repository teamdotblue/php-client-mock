<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\TestStubBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function assert;

class GuzzleClientMock implements Mock
{
    use MockLogic;

    public function mockInterface(): string
    {
        return Client::class;
    }

    public function mockPersister(): MockPersister
    {
        return $this->persister;
    }

    public function transformReturnValueOnBuild(mixed $value, TestCase $testCase): mixed
    {
        $streamMock = (new MockBuilder($testCase, Stream::class))
            ->disableOriginalConstructor()
            ->getMock();

        $streamMock
            ->expects(new InvokedCountMatcher(1))
            ->method('getContents')
            ->willReturn($value);

        $responseMock = (new MockBuilder($testCase, Response::class))
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $responseMock
            ->expects(new InvokedCountMatcher(1))
            ->method('getBody')
            ->willReturn($streamMock);

        return $responseMock;
    }

    public static function mockRequestException(int $statusCode, string $message): RequestException
    {
        /** @var RequestInterface&Stub $requestStub */
        $requestStub = (new TestStubBuilder(Request::class))
            ->disableOriginalConstructor()
            ->getStub();

        $responseStub = (new TestStubBuilder(Response::class))
            ->disableAutoReturnValueGeneration()
            ->getStub();

        $responseStub
            ->method('getStatusCode')
            ->willReturn($statusCode);

        assert($responseStub instanceof ResponseInterface);

        return new RequestException(
            $message,
            $requestStub,
            $responseStub,
        );
    }
}

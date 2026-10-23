<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock\Tests\Unit\MockPersisterData;

use TeamBlue\ClientMock\MockMethod;
use TeamBlue\ClientMock\ReturnValueTransformer;

class TestReturnValueTransformer implements ReturnValueTransformer
{
    public function __invoke(
        mixed $returnValue,
        MockMethod|bool|null $method = null,
    ): mixed {
        return $returnValue;
    }
}

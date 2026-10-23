<?php

declare(strict_types=1);

namespace TeamBlue\ClientMock;

interface ReturnValueTransformer
{
    public function __invoke(
        mixed $returnValue,
        MockMethod|null $method = null,
    ): mixed;
}

<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Command;

use Psr\Http\Message\RequestInterface;

interface RequestCommandInterface
{
    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;

    /**
     * @return bool
     */
    public function getRefresh(): bool;

    /**
     * @return array
     */
    public function getOptions(): array;
}

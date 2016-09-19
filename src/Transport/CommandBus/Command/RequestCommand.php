<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Command;

use Psr\Http\Message\RequestInterface;

final class RequestCommand implements RequestCommandInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var bool
     */
    private $refresh;

    /**
     * @param RequestInterface $request
     * @param bool $refresh
     */
    public function __construct(RequestInterface $request, bool $refresh = false)
    {
        $this->request = $request;
        $this->refresh = $refresh;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function getRefresh(): bool
    {
        return $this->refresh;
    }
}

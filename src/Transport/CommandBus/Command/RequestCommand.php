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
     * @var array
     */
    private $options;

    /**
     * @var bool
     */
    private $refresh;

    /**
     * @param RequestInterface $request
     * @param bool $refresh
     * @param array $options
     */
    public function __construct(RequestInterface $request, array $options = [], bool $refresh = false)
    {
        $this->request = $request;
        $this->options = $options;
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
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function getRefresh(): bool
    {
        return $this->refresh;
    }
}

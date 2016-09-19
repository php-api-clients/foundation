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
     * @var array
     */
    private $options;

    /**
     * @param RequestInterface $request
     * @param bool $refresh
     * @param array $options
     */
    public function __construct(RequestInterface $request, bool $refresh = false, array $options = [])
    {
        $this->request = $request;
        $this->refresh = $refresh;
        $this->options = $options;
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

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}

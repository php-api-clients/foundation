<?php declare(strict_types=1);

namespace ApiClients\Foundation\Transport\CommandBus\Command;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class SimpleRequestCommand implements RequestCommandInterface
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
     * @param string $path
     * @param bool $refresh
     */
    public function __construct(string $path, bool $refresh = false)
    {
        $this->request = new Request(
            'GET',
            $path
        );
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

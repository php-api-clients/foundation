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
     * @var array
     */
    private $options;

    /**
     * @var bool
     */
    private $refresh;

    /**
     * @param string $path
     * @param bool $refresh
     * @param array $options
     */
    public function __construct(string $path, array $options = [], bool $refresh = false)
    {
        $this->request = new Request(
            'GET',
            $path
        );
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

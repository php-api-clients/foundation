<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Cache\CacheInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use function React\Promise\resolve;
use function WyriHaximus\React\futureFunctionPromise;

class Client
{
    const DEFAULT_OPTIONS = [
        'schema' => 'https',
        'path' => '/',
        'user_agent' => 'WyriHaximus/php-api-client',
        'headers' => [],
    ];

    /**
     * @var GuzzleClient
     */
    protected $handler;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param LoopInterface $loop
     * @param GuzzleClient $handler
     * @param array $options
     */
    public function __construct(LoopInterface $loop, GuzzleClient $handler, array $options = [])
    {
        $this->loop = $loop;
        $this->handler = $handler;
        $this->options = $options + self::DEFAULT_OPTIONS;
        if (isset($this->options['cache']) && $this->options['cache'] instanceof CacheInterface) {
            $this->cache = $this->options['cache'];
        }
        $this->hydrator = new Hydrator($this, $options);
    }

    /**
     * @param string $path
     * @param bool $refresh
     * @return PromiseInterface
     */
    public function request(string $path, bool $refresh = false): PromiseInterface
    {
        return $this->requestRaw($path, $refresh)->then(function ($json) {
            return $this->jsonDecode($json);
        });
    }

    /**
     * @param string $path
     * @param bool $refresh
     * @return PromiseInterface
     */
    public function requestRaw(string $path, bool $refresh = false): PromiseInterface
    {
        if ($refresh) {
            return $this->sendRequest($path);
        }

        return $this->checkCache($path)->otherwise(function () use ($path) {
            return $this->sendRequest($path);
        });
    }

    /**
     * @param string $path
     * @return PromiseInterface
     */
    protected function checkCache(string $path): PromiseInterface
    {
        if ($this->cache instanceof CacheInterface) {
            return $this->cache->get($path);
        }

        return reject();
    }

    /**
     * @param string $path
     * @param string $method
     * @return PromiseInterface
     */
    protected function sendRequest(string $path, string $method = 'GET', bool $raw = false): PromiseInterface
    {
        $deferred = new Deferred();

        $this->handler->sendAsync(
            $this->createRequest($method, $path)
        )->then(function (ResponseInterface $response) use ($deferred) {
            $deferred->resolve($response);
        }, function ($error) use ($deferred) {
            $deferred->reject($error);
        });

        return $deferred->promise()->then(function ($response) use ($path, $raw) {
            $json = $response->getBody()->getContents();

            if ($this->cache instanceof CacheInterface) {
                $this->cache->set($path, $json);
            }

            return resolve($json);
        });
    }

    /**
     * @param string $method
     * @param string $path
     * @return RequestInterface
     */
    protected function createRequest(string $method, string $path): RequestInterface
    {
        $url = $this->getBaseURL() . $path;
        $headers = [
            'User-Agent' => $this->options['user_agent'],
        ];
        $headers += $this->options['headers'];
        return new Request($method, $url, $headers);
    }

    /**
     * @param string $json
     * @return PromiseInterface
     */
    public function jsonDecode(string $json): PromiseInterface
    {
        return futureFunctionPromise($this->loop, $json, function ($json) {
            return json_decode($json, true);
        });
    }

    /**
     * @return Hydrator
     */
    public function getHydrator(): Hydrator
    {
        return $this->hydrator;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->options['schema'] . '://' . $this->options['host'] . $this->options['path'];
    }
}

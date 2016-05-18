<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use React\Cache\CacheInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
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

    public function __construct(LoopInterface $loop, GuzzleClient $handler, array $options = [])
    {
        $this->loop = $loop;
        $this->handler = $handler;
        $this->options = self::DEFAULT_OPTIONS + $options;
        if (isset($this->options['cache']) && $this->options['cache'] instanceof CacheInterface) {
            $this->cache = $this->options['cache'];
        }
        $this->hydrator = new Hydrator($this, $options);
    }

    public function request(string $path, bool $refresh = false): PromiseInterface
    {
        if ($refresh) {
            return $this->sendRequest($path);
        }

        return $this->checkCache($path)->otherwise(function () use ($path) {
            return $this->sendRequest($path);
        });
    }

    protected function checkCache(string $path)
    {
        if ($this->cache instanceof CacheInterface) {
            return $this->cache->get($path)->then(function ($json) use ($path) {
                return $this->jsonDecode($json);
            });
        }

        return reject();
    }

    protected function sendRequest(string $path, string $method = 'GET'): PromiseInterface
    {
        $deferred = new Deferred();

        $this->handler->sendAsync(
            $this->createRequest($method, $path)
        )->then(function (ResponseInterface $response) use ($deferred) {
            $contents = $response->getBody()->getContents();
            $deferred->resolve($contents);
        }, function ($error) use ($deferred) {
            $deferred->reject($error);
        });

        return $deferred->promise()->then(function ($json) use ($path) {
            $this->cache->set($path, $json);
            return $this->jsonDecode($json);
        });
    }

    protected function createRequest(string $method, string $path)
    {
        $url = $this->options['schema'] . '://' . $this->options['host'] . $this->options['path'] . $path;
        $headers = [
            'User-Agent' => $this->options['user_agent'],
        ];
        $headers += $this->options['headers'];
        return new Request($method, $url, $headers);
    }

    protected function jsonDecode($json): PromiseInterface
    {
        return futureFunctionPromise($this->loop, $json, function ($json) {
            return json_decode($json, true);
        });
    }

    public function getHydrator(): Hydrator
    {
        return $this->hydrator;
    }

    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }
}

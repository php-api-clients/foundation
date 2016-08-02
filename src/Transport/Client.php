<?php
declare(strict_types=1);

namespace ApiClients\Foundation\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use React\Cache\CacheInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use React\Promise\RejectedPromise;
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
        return $this->requestPsr7(
            $this->createRequest($path),
            $refresh
        )->then(function ($response) {
            return resolve($response->getBody()->getContents());
        });
    }

    /**
     * @param UriInterface $uri
     * @return PromiseInterface
     */
    protected function checkCache(UriInterface $uri): PromiseInterface
    {
        if (!($this->cache instanceof CacheInterface)) {
            return reject();
        }

        $key = $this->determineCacheKey($uri);
        return $this->cache->get($key)->then(function ($document) {
            $document = json_decode($document, true);
            $response = new Response(
                $document['status_code'],
                $document['headers'],
                $document['body'],
                $document['protocol_version'],
                $document['reason_phrase']
            );

            return resolve($response);
        });
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    protected function storeCache(RequestInterface $request, ResponseInterface $response)
    {
        if (!($this->cache instanceof CacheInterface)) {
            return;
        }

        $document = [
            'body' => $response->getBody()->getContents(),
            'headers' => $response->getHeaders(),
            'protocol_version' => $response->getProtocolVersion(),
            'reason_phrase' => $response->getReasonPhrase(),
            'status_code' => $response->getStatusCode(),
        ];

        $key = $this->determineCacheKey($request->getUri());

        $this->cache->set($key, json_encode($document));
    }

    /**
     * @param UriInterface $uri
     * @return string
     */
    protected function determineCacheKey(UriInterface $uri): string
    {
        return $this->stripExtraSlashes(
            implode(
                '/',
                [
                    $uri->getScheme(),
                    $uri->getHost(),
                    $uri->getPort(),
                    $uri->getPath(),
                    md5($uri->getQuery()),
                ]
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    protected function stripExtraSlashes(string $string): string
    {
        return preg_replace('#/+#', '/', $string);
    }

    /**
     * @param RequestInterface $request
     * @param bool $refresh
     * @return PromiseInterface
     */
    public function requestPsr7(RequestInterface $request, bool $refresh = false): PromiseInterface
    {
        $promise = new RejectedPromise();

        if (!$refresh) {
            $promise = $this->checkCache($request->getUri());
        }

        return $promise->otherwise(function () use ($request) {
            $deferred = new Deferred();

            $this->handler->sendAsync(
                $request
            )->then(function (ResponseInterface $response) use ($deferred, $request) {
                $contents = $response->getBody()->getContents();
                $cacheResponse = new Response(
                    $response->getStatusCode(),
                    $response->getHeaders(),
                    $contents,
                    $response->getProtocolVersion(),
                    $response->getReasonPhrase()
                );
                $deferredResponse = new Response(
                    $response->getStatusCode(),
                    $response->getHeaders(),
                    $contents,
                    $response->getProtocolVersion(),
                    $response->getReasonPhrase()
                );
                $this->storeCache($request, $cacheResponse);
                $deferred->resolve($deferredResponse);
            }, function ($error) use ($deferred) {
                $deferred->reject($error);
            });

            return $deferred->promise();
        });
    }

    /**
     * @param string $path
     * @return RequestInterface
     */
    protected function createRequest(string $path): RequestInterface
    {
        $url = $this->getBaseURL() . $path;
        $headers = $this->getHeaders();
        return new Request('GET', $url, $headers);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [
            'User-Agent' => $this->options['user_agent'],
        ];
        $headers += $this->options['headers'];
        return $headers;
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

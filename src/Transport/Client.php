<?php
declare(strict_types=1);

namespace ApiClients\Foundation\Transport;

use ApiClients\Foundation\Hydrator\Factory as HydratorFactory;
use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Hydrator\Options as HydratorOptions;
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
        Options::SCHEMA => 'https',
        Options::PATH => '/',
        Options::USER_AGENT => 'WyriHaximus/php-api-client',
        Options::HEADERS => [],
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
        if (isset($this->options[Options::CACHE]) && $this->options[Options::CACHE] instanceof CacheInterface) {
            $this->cache = $this->options[Options::CACHE];
        }
        $this->hydrator = $this->determineHydrator();
    }

    /**
     * @return Hydrator
     * @throws \Exception
     */
    protected function determineHydrator(): Hydrator
    {
        if (isset($this->options[Options::HYDRATOR]) && $this->options[Options::HYDRATOR] instanceof Hydrator) {
            return $this->options[Options::HYDRATOR];
        }

        if (!isset($this->options[Options::HYDRATOR_OPTIONS])) {
            throw new \Exception('Missing Hydrator options');
        }

        if (!isset($this->options[Options::HYDRATOR_OPTIONS][HydratorOptions::EXTRA_PROPERTIES])) {
            $this->options[Options::HYDRATOR_OPTIONS][HydratorOptions::EXTRA_PROPERTIES] = [];
        }

        $this->options[Options::HYDRATOR_OPTIONS][HydratorOptions::EXTRA_PROPERTIES]['setTransport'] = $this;

        return HydratorFactory::create($this->options[Options::HYDRATOR_OPTIONS]);
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
                $this->storeCache(
                    $request,
                    new Response(
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $contents,
                        $response->getProtocolVersion(),
                        $response->getReasonPhrase()
                    )
                );
                $deferred->resolve(
                    new Response(
                        $response->getStatusCode(),
                        $response->getHeaders(),
                        $contents,
                        $response->getProtocolVersion(),
                        $response->getReasonPhrase()
                    )
                );
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
            'User-Agent' => $this->options[Options::USER_AGENT],
        ];
        $headers += $this->options[Options::HEADERS];
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
        return $this->options[Options::SCHEMA] . '://' . $this->options[Options::HOST] . $this->options[Options::PATH];
    }
}

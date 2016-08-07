<?php
declare(strict_types=1);

namespace ApiClients\Foundation\Transport;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use React\Dns\Resolver\Resolver;
use React\EventLoop\Factory as LoopFactory;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client as HttpClient;
use React\HttpClient\Factory as HttpClientFactory;
use React\Dns\Resolver\Factory as ResolverFactory;
use WyriHaximus\React\GuzzlePsr7\HttpClientAdapter;

class Factory
{
    /**
     * @param LoopInterface|null $loop
     * @param array $options
     * @return Client
     */
    public static function create(LoopInterface $loop = null, array $options = []): Client
    {
        if (!($loop instanceof LoopInterface)) {
            $loop = LoopFactory::create();
        }

        if (!isset($options['dns'])) {
            $options['dns'] = '8.8.8.8';
        }

        $resolver = (new ResolverFactory())->createCached($options['dns'], $loop);
        $httpClient = (new HttpClientFactory())->create($loop, $resolver);

        return self::createFromReactHttpClient(
            $httpClient,
            $resolver,
            $loop,
            $options
        );
    }

    /**
     * @param HttpClient $httpClient
     * @param Resolver $resolver
     * @param LoopInterface|null $loop
     * @param array $options
     * @return Client
     */
    public static function createFromReactHttpClient(
        HttpClient $httpClient,
        Resolver $resolver,
        LoopInterface $loop = null,
        array $options = []
    ): Client {
        return new Client(
            $loop,
            new GuzzleClient(
                [
                    'handler' => HandlerStack::create(
                        new HttpClientAdapter(
                            $loop,
                            $httpClient,
                            $resolver
                        )
                    ),
                ]
            ),
            $options
        );
    }
}

<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use ApiClients\Foundation\Transport\Options;
use ApiClients\Foundation\Transport\Response as TransportResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Phake;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use React\Cache\CacheInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\RejectedPromise;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Hydrator\Hydrator;
use function Clue\React\Block\await;
use function React\Promise\resolve;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testGetLoop()
    {
        $client = new Client(
            Factory::create(),
            Phake::mock(GuzzleClient::class),
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );
        $this->assertInstanceOf(LoopInterface::class, $client->getLoop());
    }

    public function testGetHydrator()
    {
        $client = new Client(
            Phake::mock(LoopInterface::class),
            Phake::mock(GuzzleClient::class),
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );
        $this->assertInstanceOf(Hydrator::class, $client->getHydrator());
    }

    public function testRequest()
    {
        $loop = Factory::create();

        $stream = Phake::mock(StreamInterface::class);
        Phake::when($stream)->getContents()->thenReturn('{"foo":"bar"}');

        $response = Phake::mock(ResponseInterface::class);
        Phake::when($response)->getBody()->thenReturn($stream);
        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getHeaders()->thenReturn([]);
        Phake::when($response)->getProtocolVersion()->thenReturn('1.1');
        Phake::when($response)->getReasonPhrase()->thenReturn('OK');

        $request = false;
        $handler = Phake::mock(GuzzleClient::class);
        Phake::when($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'))->thenReturnCallback(function (RequestInterface $guzzleRequest) use ($response, &$request) {
            $request = $guzzleRequest;
            return new FulfilledPromise($response);
        });

        $client = new Client(
            $loop,
            $handler,
            [
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $client->request(new Request('GET', 'http://api.example.com/status'), [], true);

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http://api.example.com/status', (string) $request->getUri());
        $this->assertSame([
            'User-Agent' => ['WyriHaximus/php-api-client'],
            'Host' => ['api.example.com'],
        ], $request->getHeaders());
    }

    public function testRequestRefreshHitAPI()
    {
        $loop = Factory::create();

        $cache = Phake::mock(CacheInterface::class);

        $stream = Phake::mock(StreamInterface::class);
        Phake::when($stream)->getContents()->thenReturn('{"foo":"bar"}');

        $response = Phake::mock(Response::class);
        Phake::when($response)->getBody()->thenReturn($stream);
        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getHeaders()->thenReturn([]);
        Phake::when($response)->getProtocolVersion()->thenReturn('1.1');
        Phake::when($response)->getReasonPhrase()->thenReturn('OK');

        $handler = Phake::mock(GuzzleClient::class);
        Phake::when($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'))->thenReturn(resolve($response));

        $client = new Client(
            $loop,
            $handler,
            [
                'cache' => $cache,
                'host' => 'api.example.com',
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $client->request(new Request('GET', 'status'), [], true);
        $loop->run();

        Phake::verify($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'));
        Phake::verify($cache, Phake::never())->get('status');
        Phake::verify($cache)->set(
            'https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e',
            '{"body":"{\"foo\":\"bar\"}","headers":[],"protocol_version":"1.1","reason_phrase":"OK","status_code":200}'
        );
    }

    public function testRequestNoCacheHitAPI()
    {
        $loop = Factory::create();

        $stream = Phake::mock(StreamInterface::class);
        Phake::when($stream)->getContents()->thenReturn('{"foo":"bar"}');

        $response = Phake::mock(Response::class);
        Phake::when($response)->getBody()->thenReturn($stream);
        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getHeaders()->thenReturn([]);
        Phake::when($response)->getProtocolVersion()->thenReturn('1.1');
        Phake::when($response)->getReasonPhrase()->thenReturn('OK');

        $handler = Phake::mock(GuzzleClient::class);
        Phake::when($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'))->thenReturn(new FulfilledPromise($response));

        $client = new Client(
            $loop,
            $handler,
            [
                'host' => 'api.example.com',
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $client->request(new Request('GET', 'status'));
        $loop->run();

        Phake::verify($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'));
    }

    public function testRequestCacheMissHitAPI()
    {
        $loop = Factory::create();

        $cache = Phake::mock(CacheInterface::class);
        Phake::when($cache)->get('https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e')->thenReturn(new RejectedPromise());

        $stream = Phake::mock(StreamInterface::class);
        Phake::when($stream)->getContents()->thenReturn('{"foo":"bar"}');

        $response = Phake::mock(Response::class);
        Phake::when($response)->getBody()->thenReturn($stream);
        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getHeaders()->thenReturn([]);
        Phake::when($response)->getProtocolVersion()->thenReturn('1.1');
        Phake::when($response)->getReasonPhrase()->thenReturn('OK');

        $handler = Phake::mock(GuzzleClient::class);
        Phake::when($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'))->thenReturn(resolve($response));

        $client = new Client(
            $loop,
            $handler,
            [
                'cache' => $cache,
                'host' => 'api.example.com',
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $result = await($client->request(new Request('GET', 'status')), $loop, 3);
        $this->assertSame('{"foo":"bar"}', $result->getBody());

        Phake::inOrder(
            Phake::verify($cache)->get('https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e'),
            Phake::verify($handler)->sendAsync(Phake::capture($request), $this->isType('array')),
            Phake::verify($cache)->set(
                'https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e',
                '{"body":"{\"foo\":\"bar\"}","headers":[],"protocol_version":"1.1","reason_phrase":"OK","status_code":200}'
            )
        );

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('https://api.example.com/status', (string)$request->getUri());
    }

    public function testRequestCacheHitIgnoreAPI()
    {
        $loop = Factory::create();

        $cache = Phake::mock(CacheInterface::class);
        Phake::when($cache)
            ->get('https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e')
            ->thenReturn(resolve('{"body":"{\"foo\":\"bar\"}","headers":[],"protocol_version":"1.1","reason_phrase":"OK","status_code":200}'));

        $handler = Phake::mock(GuzzleClient::class);

        $client = new Client(
            $loop,
            $handler,
            [
                'cache' => $cache,
                'host' => 'api.example.com',
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $result = await($client->request(new Request('GET', 'status')), $loop, 3);
        $this->assertSame('{"foo":"bar"}', $result->getBody());

        Phake::verify($cache)->get('https/api.example.com/status/d41d8cd98f00b204e9800998ecf8427e');
        Phake::verify($handler, Phake::never())->sendAsync($this->isInstanceOf(RequestInterface::class));
    }

    public function testRequestStreaming()
    {
        $loop = Factory::create();

        $cache = Phake::mock(CacheInterface::class);

        $stream = Phake::mock(StreamInterface::class);
        Phake::when($stream)->eof()
            ->thenReturn(false)
            ->thenReturn(false)
            ->thenReturn(false)
            ->thenReturn(true)
        ;
        Phake::when($stream)->getSize()
            ->thenReturn(1)
            ->thenReturn(1)
            ->thenReturn(1)
        ;
        Phake::when($stream)->read(1)
            ->thenReturn('a')
            ->thenReturn('b')
            ->thenReturn('c')
        ;

        $response = Phake::mock(Response::class);
        Phake::when($response)->getBody()->thenReturn($stream);
        Phake::when($response)->getStatusCode()->thenReturn(200);
        Phake::when($response)->getHeaders()->thenReturn([]);
        Phake::when($response)->getProtocolVersion()->thenReturn('1.1');
        Phake::when($response)->getReasonPhrase()->thenReturn('OK');

        $handler = Phake::mock(GuzzleClient::class);
        Phake::when($handler)->sendAsync($this->isInstanceOf(Request::class), $this->isType('array'))->thenReturn(resolve($response));

        $client = new Client(
            $loop,
            $handler,
            [
                'cache' => $cache,
                'host' => 'api.example.com',
                Options::HYDRATOR_OPTIONS => [],
            ]
        );

        $result = await(
            $client->request(
                new Request('GET', 'status'),
                [
                    RequestOptions::STREAM => true,
                ]
            ),
            $loop,
            3
        );
        $this->assertInstanceOf(TransportResponse::class, $result);
        $this->assertSame('', $result->getBody());

        $buffer = '';
        $result->on('data', function ($data) use (&$buffer) {
            $buffer .= $data;
        });
        $loop->run();

        $this->assertSame('abc', $buffer);

        Phake::verifyNoInteraction($cache);
    }

    public function provideGetBaseURL()
    {
        yield [
            [
                'schema' => 'http',
                'host' => 'api.wyrihaximus.net',
                Options::HYDRATOR_OPTIONS => [],
            ],
            'http://api.wyrihaximus.net/'
        ];

        yield [
            [
                'host' => 'wyrihaximus.net',
                'path' => '/api/',
                Options::HYDRATOR_OPTIONS => [],
            ],
            'https://wyrihaximus.net/api/'
        ];

        yield [
            [
                'schema' => 'gopher',
                'host' => 'thorerik.com',
                Options::HYDRATOR_OPTIONS => [],
            ],
            'gopher://thorerik.com/'
        ];
    }

    /**
     * @dataProvider provideGetBaseURL
     */
    public function testGetBaseURL(array $options, string $baseURL)
    {
        $loop = Factory::create();
        $handler = Phake::mock(GuzzleClient::class);

        $client = new Client(
            $loop,
            $handler,
            $options
        );

        $this->assertSame($baseURL, $client->getBaseURL());
    }
}

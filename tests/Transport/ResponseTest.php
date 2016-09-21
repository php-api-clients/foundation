<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use ApiClients\Foundation\Transport\Response;
use ApiClients\Tests\Foundation\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResponseTest extends TestCase
{
    public function testResponse()
    {
        $body = 'body';
        $psr7Response = $this->prophesize(ResponseInterface::class)->reveal();
        $response = new Response($body, $psr7Response);
        $this->assertSame($body, $response->getBody());
        $this->assertSame($psr7Response, $response->getResponse());
    }
}

<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Command;

use ApiClients\Foundation\Transport\CommandBus\Command\SimpleRequestCommand;
use ApiClients\Tests\Foundation\TestCase;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;

class SimpleRequestCommandTest extends TestCase
{
    /**
     * @dataProvider provideTrueFalse
     */
    public function testCommand(bool $refresh)
    {
        $method = 'GET';
        $path = '/foo/bar.json';
        $command = new SimpleRequestCommand($path, [
            RequestOptions::STREAM => true,
        ], $refresh);
        $this->assertInstanceOf(RequestInterface::class, $command->getRequest());
        $this->assertSame($method, $command->getRequest()->getMethod());
        $this->assertSame($path, $command->getRequest()->getUri()->getPath());
        $this->assertSame([
            RequestOptions::STREAM => true,
        ], $command->getOptions());
        $this->assertSame($refresh, $command->getRefresh());
    }

    public function testCommandDefaults()
    {
        $method = 'GET';
        $path = '/foo/bar.json';
        $refresh = false;
        $command = new SimpleRequestCommand($path);
        $this->assertInstanceOf(RequestInterface::class, $command->getRequest());
        $this->assertSame($method, $command->getRequest()->getMethod());
        $this->assertSame($path, $command->getRequest()->getUri()->getPath());
        $this->assertSame([], $command->getOptions());
        $this->assertSame($refresh, $command->getRefresh());
    }
}

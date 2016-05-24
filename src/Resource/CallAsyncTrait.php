<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Resource;

use function Clue\React\Block\await;
use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use Rx\React\Promise;
use WyriHaximus\ApiClient\Transport\Client;

trait CallAsyncTrait
{
    /**
     * @return Client
     */
    abstract protected function getTransport(): Client;

    /**
     * @param string $function
     * @param array ...$args
     * @return mixed
     */
    protected function callAsync(string $function, ...$args)
    {
        $classChunks = explode('\\', get_class($this));
        $class = array_pop($classChunks);
        return $this->getTransport()
            ->getHydrator()
            ->buildAsyncFromSync($class, $this)
            ->$function(...$args);
    }

    /**
     * @param ObservableInterface $observable
     * @return PromiseInterface
     */
    protected function observableToPromise(ObservableInterface $observable): PromiseInterface
    {
        return Promise::fromObservable($observable);
    }

    /**
     * @param PromiseInterface $promise
     * @return mixed
     * @throws null
     */
    protected function wait(PromiseInterface $promise)
    {
        return await(
            $promise,
            $this->getTransport()->getLoop()
        );
    }
}

<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Resource;

use function Clue\React\Block\await;
use WyriHaximus\ApiClient\Transport\Client;

trait HydrateTrait
{
    abstract protected function getTransport(): Client;

    protected function hydrate(string $class, array $json)
    {
        return $this->getTransport()->getHydrator()->hydrate($class, $json);
    }
}

<?php
declare(strict_types=1);

namespace ApiClients\Foundation\Resource;

use function Clue\React\Block\await;
use ApiClients\Foundation\Transport\Client;

trait HydrateTrait
{
    /**
     * @return Client
     */
    abstract protected function getTransport(): Client;

    /**
     * @param string $class
     * @param array $json
     * @return ResourceInterface
     */
    protected function hydrate(string $class, array $json): ResourceInterface
    {
        return $this->getTransport()->getHydrator()->hydrate($class, $json);
    }
}

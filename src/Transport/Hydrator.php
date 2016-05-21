<?php
declare(strict_types=1);

namespace WyriHaximus\ApiClient\Transport;

use GeneratedHydrator\Configuration;
use WyriHaximus\ApiClient\Resource\ResourceInterface;
use Zend\Hydrator\HydratorInterface;

class Hydrator
{
    protected $options;
    protected $transport;
    protected $hydrators = [];

    public function __construct(Client $transport, array $options)
    {
        $this->transport = $transport;
        $this->options = $options;
    }

    public function hydrateFQCN(string $class, array $json): ResourceInterface
    {
        $hydrator = $this->getHydrator($class);
        $object = $this->createObject($class);
        return $hydrator->hydrate($json, $object);
    }

    public function hydrate(string $class, array $json): ResourceInterface
    {
        $fullClassName = $this->options['namespace'] . '\\' . $this->options['resource_namespace'] . '\\' . $class;
        return $this->hydrateFQCN($fullClassName, $json);
    }

    /**
     * Takes a fully qualified class name and extracts the data for that class from the given $object
     * @param string $class
     * @param ResourceInterface $object
     * @return array
     */
    public function extractFQCN(string $class, ResourceInterface $object): array
    {
        return $this->getHydrator($class)->extract($object);
    }

    public function extract(string $class, ResourceInterface $object): array
    {
        $fullClassName = $this->options['namespace'] . '\\' . $this->options['resource_namespace'] . '\\' . $class;
        return $this->extractFQCN($fullClassName, $object);
    }

    public function buildAsyncFromSync(string $resource, ResourceInterface $object): ResourceInterface
    {
        return $this->hydrateFQCN(
            $this->options['namespace'] . '\\Async\\' . $resource,
            $this->extractFQCN(
                $this->options['namespace'] . '\\Sync\\' . $resource,
                $object
            )
        );
    }

    protected function getHydrator(string $class): HydratorInterface
    {
        if (isset($this->hydrators[$class])) {
            return $this->hydrators[$class];
        }

        $config = new Configuration($class);
        if (isset($this->options['resource_hydrator_cache_dir'])) {
            $config->setGeneratedClassesTargetDir($this->options['resource_hydrator_cache_dir']);
        }
        if (isset($this->options['resource_hydrator_namespace'])) {
            $config->setGeneratedClassesNamespace($this->options['resource_hydrator_namespace']);
        }
        $hydrator = $config->createFactory()->getHydratorClass();
        $this->hydrators[$class] = new $hydrator;

        return $this->hydrators[$class];
    }

    protected function createObject(string $class): ResourceInterface
    {
        $object = new $class();
        $object->setTransport($this->transport);
        if (isset($this->options['setters'])) {
            foreach ($this->options['setters'] as $method => $argument) {
                if (!method_exists($object, $method)) {
                    continue;
                }
                $object->$method($argument);
            }
        }
        return $object;
    }
}

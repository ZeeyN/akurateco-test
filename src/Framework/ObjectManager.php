<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Framework;

use LogicException;
use NikitaRusakov\AkuratecoTest\Framework\DefinitionReaders\ConstructorDefinitionReader;
use ReflectionException;

class ObjectManager
{
    /**
     * @var ObjectManager
     */
    private static self $instance;

    /**
     * @var array
     */
    private static array $objectPool = [];

    /**
     * @var array
     */
    private array $creationStack;

    /**
     * @var ConstructorDefinitionReader
     */
    private ConstructorDefinitionReader $definitionReader;

    /**
     * ObjectManager constructor
     */
    private function __construct(ConstructorDefinitionReader $definitionReader)
    {
        $this->definitionReader = $definitionReader;
    }

    /**
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    public function create(string $className): object
    {
        $parameters = $this->definitionReader->read($className);
        $stack = [];
        $this->creationStack[$className] = true;

        foreach ($parameters as $type) {
            if (isset($this->creationStack[$type])) {
                throw new LogicException('Cyclomatic dependency');
            }

            $stack[] = $this->get($type);
        }

        unset($this->creationStack[$className]);

        return new $className(...$stack);
    }

    /**
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    public function get(string $className): object
    {
        if (!isset(self::$objectPool[$className])) {
            self::$objectPool[$className] = $this->create($className);
        }

        return self::$objectPool[$className];
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (empty(self::$instance) || !self::$instance instanceof self) {
            $definitionReader = new ConstructorDefinitionReader();
            self::$instance = new self($definitionReader);
        }

        return self::$instance;
    }
}

<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Framework\DefinitionReaders;

use ReflectionClass;
use ReflectionException;

class ConstructorDefinitionReader implements DefinitionReaderInterface
{
    /**
     * [
     *      'name' => 'type'
     * ]
     * @param string $className
     * @return array
     * @throws ReflectionException
     */
    public function read(string $className): array
    {
        $result = [];
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if (!empty($constructor)) {
            foreach ($constructor->getParameters() as $constructorParam) {
                $result[$constructorParam->getName()] = $constructorParam->getType()->getName();
            }
        }

        return $result;
    }
}

<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Framework;

use Exception;

abstract class AbstractFactory
{
    /**
     * @param string|null $className
     * @return object
     * @throws Exception
     */
    protected function createObject(string $className = null): object
    {
        $className = static::CLASS_NAME ?? $className;

        try {
            $objectManager = ObjectManager::getInstance();
            return $objectManager->create($className);
        } catch (Exception) {
            throw new Exception('ERROR: ' . __CLASS__ . '::Class name was not provided');
        }
    }

    /**
     * @return object
     * @throws Exception
     */
    public function create(): object
    {
        return $this->createObject();
    }
}

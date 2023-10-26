<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Framework\DefinitionReaders;

interface DefinitionReaderInterface
{
    /**
     * @param string $className
     * @return array
     */
    public function read(string $className): array;
}

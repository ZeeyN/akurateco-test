<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Response;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getAction(): string;

    /**
     * @return string
     */
    public function getResult(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return array
     */
    public function getBody(): array;
}

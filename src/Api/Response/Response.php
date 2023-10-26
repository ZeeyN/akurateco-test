<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Response;

use NikitaRusakov\AkuratecoTest\Framework\DataObject;

class Response extends DataObject implements ResponseInterface
{
    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->getData('status');
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->getData('action');
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->getData('result');
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->getData('body');
    }

    /**
     * @param $responseStr
     * @return false|int
     */
    public static function extractCode($responseStr): bool|int
    {
        preg_match("|^HTTP/[\d.x]+ (\d+)|", $responseStr, $match);

        if (isset($match[1])) {
            return (int) $match[1];
        } else {
            return false;
        }
    }
}

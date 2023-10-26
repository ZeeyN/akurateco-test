<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Response;

use Exception;
use NikitaRusakov\AkuratecoTest\Framework\AbstractFactory;

class ResponseFactory extends AbstractFactory
{
    public const CLASS_NAME = Response::class;

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws Exception
     */
    public function create(array $data = []): ResponseInterface
    {
        return parent::create()->setData($data);
    }
}

<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Client;

use Exception;
use NikitaRusakov\AkuratecoTest\Api\Client\Adapter\Curl;
use NikitaRusakov\AkuratecoTest\Api\Client\Adapter\CurlFactory;
use NikitaRusakov\AkuratecoTest\Api\Response\ResponseFactory;
use NikitaRusakov\AkuratecoTest\Api\Response\ResponseInterface;
use NikitaRusakov\AkuratecoTest\Api\Method\Sale;

class Client
{
    public const CONNECTION_TIMEOUT = 60;

    /**
     * @var array
     */
    protected array $auth = [];

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @param CurlFactory $curlFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly ResponseFactory $responseFactory
    ) {
    }

    /**
     * @param array $auth
     * @return $this
     */
    public function setAuth(array $auth): self
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @param array $data
     * @return ResponseInterface
     * @throws Exception
     */
    public function sale(array $data): ResponseInterface
    {
        $params = [
            Sale::action->name => 'SALE'
        ];
        $authData = $this->prepareAuth($data);
        $data = array_merge($authData, $data);

        foreach (Sale::cases() as $case) {
            if (isset($data[$case->name])) {
                $params[$case->name] = $data[$case->name];
            }
        }

        return $this->request($authData['url'], http_build_query($params));
    }

    /**
     * @param string $url
     * @param string $params
     * @param string $method
     * @return ResponseInterface
     * @throws Exception
     */
    public function request(
        string $url,
        string $params = '',
        string $method = 'POST'
    ): ResponseInterface {
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setConfig(['timeout' => self::CONNECTION_TIMEOUT, 'header' => false]);
        $curl->write($method, $url, '1.1', $this->getHeaders(), $params);
        $responseData = $curl->read();
        $responseData = json_decode($responseData, true) ?? [];
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);

        if (!in_array($httpCode, [200, 204])) {
            throw new Exception('Invalid request: ' . $responseData['decline_reason']);
        }

        $curl->close();

        return $this->responseFactory->create($responseData);
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $name => $value) {
            $headers[] = implode(': ', [$name, $value]);
        }

        return $headers;
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareAuth(array $data): array
    {
        return [
            'url' => $this->auth['url'],
            Sale::client_key->name => $this->auth[Sale::client_key->name],
            Sale::hash->name => $this->prepareHash($data)
        ];
    }

    /**
     * @param array $data
     * @return string
     */
    private function prepareHash(array $data): string
    {
        return md5(
            strtoupper(
                strrev($data[Sale::payer_email->name]) .
                $this->auth['client_pass'] .
                strrev(
                    substr($data[Sale::card_number->name], 0, 6) .
                    substr($data[Sale::card_number->name], -4)
                )
            )
        );
    }
}

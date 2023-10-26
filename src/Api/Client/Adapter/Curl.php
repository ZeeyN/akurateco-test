<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Api\Client\Adapter;

use CurlHandle;
use NikitaRusakov\AkuratecoTest\Api\Response\Response;

class Curl
{
    /**
     * @var array
     */
    protected array $config = [
        'protocols' => (CURLPROTO_HTTP | CURLPROTO_HTTPS),
        'verifyhost' => 2
    ];

    /**
     * @var array
     */
    protected array $allowedParams = [
        'timeout'      => CURLOPT_TIMEOUT,
        'maxredirects' => CURLOPT_MAXREDIRS,
        'proxy'        => CURLOPT_PROXY,
        'ssl_cert'     => CURLOPT_SSLCERT,
        'userpwd'      => CURLOPT_USERPWD,
        'useragent'    => CURLOPT_USERAGENT,
        'referer'      => CURLOPT_REFERER,
        'protocols'    => CURLOPT_PROTOCOLS,
        'verifypeer'   => CURLOPT_SSL_VERIFYPEER,
        'verifyhost'   => CURLOPT_SSL_VERIFYHOST,
        'sslversion'   => CURLOPT_SSLVERSION,
    ];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var bool|CurlHandle|null
     */
    protected bool|CurlHandle|null $resource;

    /**
     * @return CurlHandle
     */
    protected function getResource(): CurlHandle
    {
        if (empty($this->resource) || !$this->resource instanceof CurlHandle) {
            $this->resource = curl_init();
        }

        return $this->resource;
    }

    /**
     * @return $this
     */
    protected function applyConfig(): self
    {
        foreach ($this->options as $option => $value) {
            curl_setopt($this->getResource(), $option, $value);
        }

        foreach ($this->getDefaultConfig() as $option => $value) {
            curl_setopt($this->getResource(), $option, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    private function getDefaultConfig(): array
    {
        $config = [];

        foreach (array_keys($this->config) as $param) {
            if (array_key_exists($param, $this->allowedParams)) {
                $config[$this->allowedParams[$param]] = $this->config[$param];
            }
        }

        return $config;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options = []): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param int $option
     * @param mixed $value
     * @return $this
     */
    public function addOption(int $option, mixed $value): self
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config = []): self
    {
        foreach ($config as $key => $value) {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function connect(): self
    {
        return $this->applyConfig();
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $httpVer
     * @param array $headers
     * @param string $body
     * @return string
     */
    public function write(
        string $method,
        string $url,
        string $httpVer = '1.1',
        array $headers = [],
        string $body = ''
    ): string {
        $this->applyConfig();

        curl_setopt($this->getResource(), CURLOPT_URL, $url);
        curl_setopt($this->getResource(), CURLOPT_RETURNTRANSFER, true);

        switch ($method) {
            case 'POST':
                curl_setopt($this->getResource(), CURLOPT_POST, true);
                curl_setopt($this->getResource(), CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($this->getResource(), CURLOPT_POSTFIELDS, $body);
                break;
            case 'PUT':
                curl_setopt($this->getResource(), CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($this->getResource(), CURLOPT_POSTFIELDS, $body);
                break;
            case 'GET':
                curl_setopt($this->getResource(), CURLOPT_HTTPGET, true);
                curl_setopt($this->getResource(), CURLOPT_CUSTOMREQUEST, 'GET');
                break;
            case 'DELETE':
                curl_setopt($this->getResource(), CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($this->getResource(), CURLOPT_POSTFIELDS, $body);
                break;
        }

        if ($httpVer === '1.1') {
            curl_setopt($this->getResource(), CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        } elseif ($httpVer === '1.0') {
            curl_setopt($this->getResource(), CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        }

        if (is_array($headers)) {
            curl_setopt($this->getResource(), CURLOPT_HTTPHEADER, $headers);
        }

        $header = $this->config['header'] ?? true;
        curl_setopt($this->getResource(), CURLOPT_HEADER, $header);

        return $body;
    }

    /**
     * @return bool|string
     */
    public function read(): bool|string
    {
        $response = curl_exec($this->getResource());

        if ($response === false) {
            return '';
        }

        // Remove 100 and 101 responses headers
        while (Response::extractCode($response) == 100 || Response::extractCode($response) == 101) {
            $response = preg_split('/^\r?$/m', $response, 2);
            $response = trim($response[1]);
        }

        preg_replace('/Transfer-Encoding:\s+chunked\r?\n/i', '', $response);

        return $response;
    }

    /**
     * @return $this
     */
    public function close(): self
    {
        curl_close($this->getResource());
        $this->resource = null;

        return $this;
    }

    /**
     * @return int
     */
    public function getErrno(): int
    {
        return curl_errno($this->getResource());
    }

    /**
     * @return string
     */
    public function gerError(): string
    {
        return curl_error($this->getResource());
    }

    /**
     * @param int $opt
     * @return mixed
     */
    public function getInfo(int $opt = 0): mixed
    {
        return curl_getinfo($this->getResource(), $opt);
    }
}

<?php

declare(strict_types=1);

namespace NikitaRusakov\AkuratecoTest\Framework;

use ArrayAccess;
use Exception;

class DataObject implements ArrayAccess
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var array
     */
    protected static array $underscoreCache = [];

    /**
     * @param string|array $key
     * @param mixed|null $value
     * @return $this
     */
    public function setData(string|array $key, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string|array|null $key
     * @return $this
     */
    public function unsetData(string|array $key = null): self
    {
        if (!$key) {
            $this->setData([]);
        } elseif (is_string($key)) {
            if (isset($this->data[$key]) || array_key_exists($key, $this->data)) {
                unset($this->data[$key]);
            }
        } elseif (is_array($key)) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string|int|null $index
     * @return mixed
     */
    public function getData(string $key = '', string|int $index = null): mixed
    {
        if (empty($key)) {
            return $this->data;
        }

        if (str_contains($key, '/')) {
            $data = $this->getDataByPath($key);
        } else {
            $data = $this->getDirectData($key);
        }

        if ($index) {
            if (is_array($data)) {
                $data = $data[$index] ?? null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = $data[$index] ?? null;
            } elseif ($data instanceof DataObject) {
                $data = $data->getData($index);
            } else {
                $data = null;
            }
        }

        return $data;
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function getDataByPath(string $path): mixed
    {
        $keys = explode('/', $path);
        $data = $this->data;

        foreach ($keys as $key) {
            if (is_array($data) && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getDataByKey(string $key): mixed
    {
        return $this->getDirectData($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getDirectData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasData(string $key = ''): bool
    {
        if (empty($key) || !is_string($key)) {
            $result = !empty($this->data);
        } else {
            $result = array_key_exists($key, $this->data);
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function __call(string $method, array $args)
    {
        $baseMethod = substr($method, 0, 3);
        $key = $this->underscore(substr($method, 3));
        $index = $args[0] ?? null;

        switch ($baseMethod) {
            case 'get':
                return $this->getData($key, $index);
            case 'set':
                return $this->setData($key, $index);
            case 'uns':
                return $this->unsetData($key);
            case 'has':
                return isset($this->data[$key]);
        }

        throw new Exception('Invalid method ' . __CLASS__ . "::$method");
    }

    /**
     * @param string $name
     * @return string
     */
    protected function underscore(string $name): string
    {
        if (!isset(self::$underscoreCache[$name])) {
            $result = strtolower(trim(preg_replace('/([A-Z]|\d+)/', "_$1", $name), '_'));
            self::$underscoreCache[$name] = $result;
        }

        return self::$underscoreCache[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]) || array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }
}

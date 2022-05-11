<?php

namespace EmgSystems\Train4Sustain\Request;

/**
 * Implementation of curl interface
 */
class Curl implements CurlInterface
{
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var string|null
     */
    protected ?string $response;

    /**
     * @inheritDoc
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('curl');
    }

    /**
     * @inheritDoc
     */
    public function init(string $url = null): void
    {
        $this->handle = curl_init($url);
    }

    /**
     * @inheritDoc
     */
    public function setOption(int $option, string $value): bool
    {
        return curl_setopt($this->handle, $option, $value);
    }

    /**
     * @inheritDoc
     */
    public function setOptions(array $options): bool
    {
        return curl_setopt_array($this->handle, $options);
    }

    /**
     * @inheritDoc
     */
    public function exec(): void
    {
        $this->response = null;
        $this->response = $this->getAndValidateResponse();
    }

    /**
     * @inheritDoc
     */
    public function errorNumber(): int
    {
        return curl_errno($this->handle);
    }

    /**
     * @inheritDoc
     */
    public function errorMessage(): string
    {
        return curl_error($this->handle);
    }

    /**
     * @inheritDoc
     */
    public function getInfo(int $option): ?string
    {
        if ($option <= 0) {
            return null;
        }
        return curl_getinfo($this->handle, $option);
    }

    /**
     * @inheritDoc
     */
    public function getAllInfo(): array
    {
        return curl_getinfo($this->handle);
    }

    /**
     * @inheritDoc
     * @throws CurlException
     */
    public function getHeader(): ?string
    {
        $headerSize = (int)$this->getInfo(CURLINFO_HEADER_SIZE);
        $header = substr($this->response, 0, $headerSize);
        if (!$header) {
            throw new CurlException('Unprocessable response');
        }
        return $header;
    }

    /**
     * @inheritDoc
     * @throws CurlException
     */
    public function getBody(): ?string
    {
        $headerSize = (int)$this->getInfo(CURLINFO_HEADER_SIZE);
        $body = substr($this->response, $headerSize);
        if (!$body) {
            throw new CurlException('Unprocessable response');
        }
        return $body;
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        curl_close($this->handle);
    }

    /**
     * @return string
     * @throws CurlException
     */
    protected function getAndValidateResponse(): string
    {
        $response = curl_exec($this->handle);
        $errorNumber = $this->errorNumber();
        if ($errorNumber) {
            throw new CurlException($this->errorMessage(), $errorNumber);
        }
        return $response;
    }
}

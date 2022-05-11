<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace EmgSystems\Train4Sustain\Request;

/**
 * Curl interface
 */
interface CurlInterface
{
    /**
     * @return bool
     */
    public static function isAvailable(): bool;

    /**
     * @param string|null $url
     */
    public function init(string $url = null): void;

    /**
     * @param int    $option
     * @param string $value   The option value. Provide '' or '1' for boolean values.
     *
     * @return bool
     */
    public function setOption(int $option, string $value): bool;

    /**
     * @param array $options
     *
     * @return bool
     */
    public function setOptions(array $options): bool;

    /**
     * @return void
     * @throws CurlException
     */
    public function exec(): void;

    /**
     * @return int
     */
    public function errorNumber(): int;

    /**
     * @return string
     */
    public function errorMessage(): string;

    /**
     * @param int $option
     *
     * @return string|null
     */
    public function getInfo(int $option): ?string;

    /**
     * @return array
     */
    public function getAllInfo(): array;

    /**
     * Retrieves the raw response header.
     *
     * @return string|null
     */
    public function getHeader(): ?string;

    /**
     * Retrieves the raw response body.
     *
     * @return string|null
     */
    public function getBody(): ?string;

    /**
     * Frees up the curl resource.
     */
    public function close(): void;
}

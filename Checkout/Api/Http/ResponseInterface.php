<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Http;

/**
 * Https client response data model interface.
 */
interface ResponseInterface
{
    /**
     * Retrieve response status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Retrieve response errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Retrieve response body.
     *
     * @return array
     */
    public function getBody(): array;
}

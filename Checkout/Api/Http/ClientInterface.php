<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Http;

use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;

/**
 * Http client interface to make requests to Bold side.
 */
interface ClientInterface
{
    public const BOLD_API_VERSION_DATE = "2022-10-14";

    /**
     * Perform http request to bold.
     *
     * @param string $method
     * @param string $url
     * @param array|null $data
     * @return \Bold\Checkout\Api\Data\Http\Client\ResponseInterface
     * @throws \Exception
     */
    public function call(string $method, string $url, array $data = null): ResponseInterface;
}

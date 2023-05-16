<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;

/**
 * Http client interface to make requests to Bold side|Platform.
 */
interface ClientInterface
{
    /**
     * Perform get http request to bold|platform.
     *
     * @param int $websiteId
     * @param string $url
     * @return ResultInterface
     * @throws \Exception
     */
    public function get(int $websiteId, string $url): ResultInterface;

    /**
     * Perform post http request to bold|platform.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function post(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform put http request to bold|platform.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function put(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform patch http request to bold|platform.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function patch(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform delet http request to bold|platform.
     *
     * @param int $websiteId
     * @param string $url
     * @return ResultInterface
     * @throws \Exception
     */
    public function delete(int $websiteId, string $url): ResultInterface;
}

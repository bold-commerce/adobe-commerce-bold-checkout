<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;

/**
 * Http client interface to perform http requests to Platform Connector and Bold Checkout API.
 * 
 * @see \Bold\Checkout\Model\Http\BoldClient
 * @see \Bold\Checkout\Model\Http\BoldStorefrontClient
 * @api
 */
interface ClientInterface
{
    /**
     * Perform get http request.
     *
     * @param int $websiteId
     * @param string $url
     * @return ResultInterface
     * @throws \Exception
     */
    public function get(int $websiteId, string $url): ResultInterface;

    /**
     * Perform post http request.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function post(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform put http request.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function put(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform patch http request.
     *
     * @param int $websiteId
     * @param string $url
     * @param array|null $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function patch(int $websiteId, string $url, array $data): ResultInterface;

    /**
     * Perform delete http request.
     *
     * @param int $websiteId
     * @param string $url
     * @param array $data
     * @return ResultInterface
     * @throws \Exception
     */
    public function delete(int $websiteId, string $url, array $data): ResultInterface;
}

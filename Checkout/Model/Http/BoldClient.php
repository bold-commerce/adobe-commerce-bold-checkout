<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;

/**
 * Client to perform http request to Bold.
 */
class BoldClient implements ClientInterface
{
    public const URL = 'https://api.boldcommerce.com/';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Curl
     */
    private $client;

    /**
     * @var UserAgent
     */
    private $userAgent;

    /**
     * @param ConfigInterface $config
     * @param Curl $curl
     * @param UserAgent $userAgent
     */
    public function __construct(
        ConfigInterface $config,
        Curl $curl,
        UserAgent $userAgent
    ) {
        $this->config = $config;
        $this->client = $curl;
        $this->userAgent = $userAgent;
    }

    /**
     * @inheritDoc
     */
    public function call(int $websiteId, string $method, string $url, array $data = null): ResponseInterface
    {
        $apiToken = $this->config->getApiToken($websiteId);
        $shopId = $this->config->getShopId($websiteId);
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgent(),
            'Bold-API-Version-Date' => self::BOLD_API_VERSION_DATE,
        ];
        $url = self::URL . str_replace('{{shopId}}', $shopId, $url);

        return $this->client->sendRequest($method, $url, $headers, $data);
    }
}

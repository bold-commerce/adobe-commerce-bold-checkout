<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Curl;

/**
 * M2 Platform Connector Http Client.
 */
class PlatformClient implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Curl
     */
    private $client;

    /**
     * @param ConfigInterface $config
     * @param Curl $curl
     */
    public function __construct(
        ConfigInterface $config,
        Curl $curl
    ) {
        $this->config = $config;
        $this->client = $curl;
    }

    /**
     * @inheritDoc
     */
    public function call(int $websiteId, string $method, string $url, array $data = null): ResponseInterface
    {
        $secret = $this->config->getSharedSecret($websiteId);
        $shopId = $this->config->getShopId($websiteId);
        $headers = [
            'Authorization' => 'Bearer ' . $secret,
            'Content-Type' => 'application/json',
        ];
        $url = $this->config->getPlatformConnectorUrl($websiteId) . str_replace('{{shopId}}', $shopId, $url);

        return $this->client->sendRequest($method, $url, $headers, $data);
    }
}

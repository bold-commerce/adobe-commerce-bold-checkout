<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\ConfigInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\BoldShopIdentifier;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;
use stdClass;

/**
 * Client to perform http request to Bold.
 */
class BoldClient implements ClientInterface
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
     * @var UserAgent
     */
    private $userAgent;

    /**
     * @var BoldShopIdentifier
     */
    private $boldShopIdentifier;

    /**
     * @param ConfigInterface $config
     * @param Curl $curl
     * @param UserAgent $userAgent
     * @param BoldShopIdentifier $boldShopIdentifier
     */
    public function __construct(
        ConfigInterface $config,
        Curl $curl,
        UserAgent $userAgent,
        BoldShopIdentifier $boldShopIdentifier
    ) {
        $this->config = $config;
        $this->client = $curl;
        $this->userAgent = $userAgent;
        $this->boldShopIdentifier = $boldShopIdentifier;
    }

    /**
     * @inheritDoc
     */
    public function call(string $method, string $url, array $data = null): stdClass
    {
        $apiToken = $this->config->getApiToken();;
        $shopId = $this->boldShopIdentifier->getShopIdentifier();
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgent(),
            'Bold-API-Version-Date' => self::BOLD_API_VERSION_DATE,
        ];
        $url = $this->config->getApiUrl() . '/' . ltrim(str_replace('{{shopId}}', $shopId, $url), '/');

        return json_decode($this->client->sendRequest($method, $url, $headers, $data));
    }
}

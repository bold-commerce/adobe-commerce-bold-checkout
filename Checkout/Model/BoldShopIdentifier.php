<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\ConfigInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Exception;

/**
 * Retrieve shop identifier from Bold.
 */
class BoldShopIdentifier
{
    private const SHOP_INFO_URL = '/shops/v1/info';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UserAgent
     */
    private $userAgent;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @param ConfigInterface $config
     * @param UserAgent $userAgent
     * @param Curl $curl
     */
    public function __construct(ConfigInterface $config, UserAgent $userAgent, Curl $curl)
    {
        $this->config = $config;
        $this->userAgent = $userAgent;
        $this->curl = $curl;
    }

    /**
     * Retrieve shop identifier from Bold.
     *
     * @return string
     * @throws Exception
     */
    public function getShopIdentifier(): string
    {
        $shopIdentifier = $this->config->getShopIdentifier();
        if ($shopIdentifier) {
            return $shopIdentifier;
        }
        $apiToken = $this->config->getApiToken();
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgent(),
            'Bold-API-Version-Date' => ClientInterface::BOLD_API_VERSION_DATE,
        ];
        $url = $this->config->getApiUrl() . self::SHOP_INFO_URL;
        $shopInfo = $this->curl->sendRequest('GET', $url, $headers);
        if ($shopInfo->getErrors()) {
            $error = current($shopInfo->getErrors());
            throw new Exception($error);
        }
        $this->config->setShopIdentifier($shopInfo->getBody()['shop_identifier']);

        return $this->config->getShopIdentifier();
    }
}

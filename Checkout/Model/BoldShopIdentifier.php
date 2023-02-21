<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\ConfigInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Magento\Framework\Exception\LocalizedException;

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
     * @throws LocalizedException
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
        $shopInfo = json_decode($this->curl->sendRequest('GET', $url, $headers));
        if (isset($shopInfo->error)) {
            throw new LocalizedException(__($shopInfo->error_description));
        }
        $this->config->setShopIdentifier($shopInfo->shop_identifier);

        return $this->config->getShopIdentifier();
    }
}

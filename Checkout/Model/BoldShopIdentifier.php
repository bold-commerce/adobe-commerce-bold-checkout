<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Exception;
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
     * @param int $websiteId
     * @return string
     * @throws Exception
     */
    public function getShopIdentifier(int $websiteId): string
    {
        $shopIdentifier = $this->config->getShopIdentifier($websiteId);
        if ($shopIdentifier) {
            return $shopIdentifier;
        }
        $apiToken = $this->config->getApiToken($websiteId);
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgent(),
            'Bold-API-Version-Date' => ClientInterface::BOLD_API_VERSION_DATE,
        ];
        $url = $this->config->getApiUrl($websiteId) . self::SHOP_INFO_URL;
        $shopInfo = $this->curl->sendRequest('GET', $url, $headers);
        if ($shopInfo->getErrors()) {
            $error = current($shopInfo->getErrors());
            throw new Exception($error);
        }
        $this->config->setShopIdentifier($websiteId, $shopInfo->getBody()['shop_identifier']);
        $shopIdentifier = $this->config->getShopIdentifier($websiteId);
        if ($shopIdentifier === null) {
            throw new LocalizedException(__('There is no shop identifier for website id "%s"', $websiteId));
        }
        return $shopIdentifier;
    }
}

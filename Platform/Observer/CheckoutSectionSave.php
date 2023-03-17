<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Curl;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observe 'admin_system_config_changed_section_checkout' event and re-new shop identifier.
 */
class CheckoutSectionSave implements ObserverInterface
{
    private const SHOP_INFO_URL = 'https://api.boldcommerce.com/shops/v1/info';

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
    public function __construct(
        ConfigInterface $config,
        UserAgent $userAgent,
        Curl $curl
    ) {
        $this->config = $config;
        $this->userAgent = $userAgent;
        $this->curl = $curl;
    }

    /**
     * Retrieve shop id from Bold and save it in config.
     *
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        $websiteId = (int)$event->getWebsite();
        $apiToken = $this->config->getApiToken($websiteId);
        $headers = [
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgent(),
            'Bold-API-Version-Date' => ClientInterface::BOLD_API_VERSION_DATE,
        ];
        $shopInfo = $this->curl->sendRequest('GET', self::SHOP_INFO_URL, $headers);
        if ($shopInfo->getErrors()) {
            $error = current($shopInfo->getErrors());
            throw new Exception($error);
        }
        $this->config->setShopId($websiteId, $shopInfo->getBody()['shop_identifier']);
    }
}

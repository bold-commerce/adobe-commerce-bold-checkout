<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\BoldIntegration;
use Bold\Checkout\Model\ConfigInterface;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observe 'admin_system_config_changed_section_checkout' event and re-new shop identifier.
 */
class CheckoutSectionSave implements ObserverInterface
{
    private const SHOP_INFO_URL = 'shops/v1/info';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var BoldIntegration
     */
    private $updateIntegration;

    /**
     * @param ConfigInterface $config
     * @param ClientInterface $client
     * @param BoldIntegration $updateIntegration
     */
    public function __construct(
        ConfigInterface $config,
        ClientInterface $client,
        BoldIntegration $updateIntegration
    ) {
        $this->client = $client;
        $this->config = $config;
        $this->updateIntegration = $updateIntegration;
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
        if (!$this->config->isCheckoutEnabled($websiteId) || $websiteId === 0) {
            return;
        }
        $this->config->setShopId($websiteId, null);
        $shopInfo = $this->client->get($websiteId, self::SHOP_INFO_URL);
        if ($shopInfo->getErrors()) {
            $error = current($shopInfo->getErrors());
            throw new Exception($error);
        }
        $this->config->setShopId($websiteId, $shopInfo->getBody()['shop_identifier']);
        $changedPaths = $event->getChangedPaths();
        $this->updateIntegration->update($changedPaths, $websiteId);
    }
}

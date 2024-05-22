<?php

declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\PaymentStyleManagementInterface;
use Bold\Checkout\Model\Config;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\PaymentStyleManagement\PaymentStyleBuilderFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Observe 'admin_system_config_changed_section_checkout' event and sync payment iframe styles.
 */
class SyncPaymentStyle implements ObserverInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PaymentStyleManagementInterface
     */
    private $paymentStyleManagement;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PaymentStyleBuilderFactory
     */
    private $paymentStyleBuilderFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ConfigInterface $config
     * @param PaymentStyleManagementInterface $paymentStyleManagement
     * @param StoreManagerInterface $storeManager
     * @param PaymentStyleBuilderFactory $paymentStyleBuilderFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigInterface                 $config,
        PaymentStyleManagementInterface $paymentStyleManagement,
        StoreManagerInterface           $storeManager,
        PaymentStyleBuilderFactory      $paymentStyleBuilderFactory,
        SerializerInterface             $serializer
    ) {
        $this->config = $config;
        $this->paymentStyleManagement = $paymentStyleManagement;
        $this->storeManager = $storeManager;
        $this->paymentStyleBuilderFactory = $paymentStyleBuilderFactory;
        $this->serializer = $serializer;
    }

    /**
     * Sync payment iframe styles.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        $websiteId = (int)$event->getWebsite() ?: (int)$this->storeManager->getWebsite(true)->getId();
        if (!$this->config->isCheckoutEnabled($websiteId)
            || !in_array(Config::PATH_PAYMENT_CSS, $event->getChangedPaths())
        ) {
            return;
        }

        $style = preg_replace('/\s+/', ' ', $this->serializer->unserialize($this->config->getPaymentCss($websiteId)));
        if (!empty($style)) {
            $styleBuilder = $this->paymentStyleBuilderFactory->create();
            $styleBuilder->addCssRule($style);
            $this->paymentStyleManagement->update($websiteId, $styleBuilder->build());
        } else {
            $this->paymentStyleManagement->delete($websiteId);
        }
    }
}

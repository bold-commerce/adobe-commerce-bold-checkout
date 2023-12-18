<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Config;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Store\Model\StoreManagerInterface;

/**
 *  Bold Payment Title Value Handler.
 */
class TitleValueHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ConfigInterface $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $subject, $storeId = null)
    {
        /** @var PaymentDataObject $paymentObject */
        $paymentObject = $subject['payment'] ?? null;
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        if (!$paymentObject || !$paymentObject->getPayment()) {
            if (!$websiteId) {
                $store = $this->storeManager->getDefaultStoreView();
                $websiteId = (int)$store->getWebsiteId();
            }

            return $this->config->getPaymentTitle($websiteId);
        }
        $ccLast4 = $paymentObject->getPayment()->getCcLast4();
        $ccType = $paymentObject->getPayment()->getCcType();
        if (!$ccLast4 || !$ccType) {
            if (!$websiteId) {
                $orderAdapter = $paymentObject->getOrder();
                $storeId = $orderAdapter->getStoreId();
                $store = $this->storeManager->getStore($storeId);
                $websiteId = (int)$store->getWebsiteId();
            }

            return $this->config->getPaymentTitle($websiteId);
        }

        return strlen($ccLast4) === 4
            ? $ccType . ': ending in ' . $ccLast4
            : $ccType . ': ' . $ccLast4;
    }
}

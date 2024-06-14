<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *  Bold Payment Title Value Handler.
 */
class TitleValueHandler implements ValueHandlerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $path;

    /**
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param string $path
     */
    public function __construct(
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        string $path
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->path = $path;
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
            return $this->config->getValue($this->path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
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
            return $this->config->getValue($this->path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        }

        return strlen($ccLast4) === 4
            ? $ccType . ': ending in ' . $ccLast4
            : $ccType . ': ' . $ccLast4;
    }
}

<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    private const URL = 'https://api.boldcommerce.com/checkout/storefront/';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param Session $checkoutSession
     * @param ConfigInterface $config
     * @param ClientInterface $client
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $checkoutSession,
        ConfigInterface $config,
        ClientInterface $client,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return [];
        }
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $shopId = $this->config->getShopId($websiteId);
        $orderId = $boldCheckoutData['data']['public_order_id'];
        $jwtToken = $boldCheckoutData['data']['jwt_token'];
        return [
            'bold' => [
                'payment' => [
                    'iframeSrc' => $this->getIframeSrc(),
                    'title' => __('Bold Payments'),
                    'method' => Service::CODE,
                ],
                'shopId' => $shopId,
                'customerIsGuest' => $this->checkoutSession->getQuote()->getCustomerIsGuest(),
                'publicOrderId' => $orderId,
                'jwtToken' => $jwtToken,
                'billingAddressUrl' => self::URL . $shopId . '/' . $orderId . '/addresses/billing',
                'url' => self::URL . $shopId . '/' . $orderId . '/',
            ],
        ];
    }

    private function getIframeSrc(): ?string
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return null;
        }
        $websiteId = (int)$this->storeManager->getWebsite()->getId();
        $shopId = $this->config->getShopId($websiteId);
        $styles = $this->getStyles();
        if ($styles) {
            $this->client->post($websiteId, 'payments/styles', $styles);
        }
        $orderId = $boldCheckoutData['data']['public_order_id'];
        $jwtToken = $boldCheckoutData['data']['jwt_token'];
        return self::URL . $shopId . '/' . $orderId . '/payments/iframe?token=' . $jwtToken;
    }

    private function getStyles()
    {
        return null;
    }
}

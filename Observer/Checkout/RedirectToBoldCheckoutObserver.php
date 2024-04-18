<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\RedirectToBoldCheckout\IsOrderInitializationAllowedInterface;
use Bold\Checkout\Model\RedirectToBoldCheckout\IsRedirectToBoldCheckoutAllowedInterface;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Redirect to bold checkout if allowed.
 */
class RedirectToBoldCheckoutObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var InitOrderFromQuote
     */
    private $initOrderFromQuote;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var IsOrderInitializationAllowedInterface
     */
    private  $isOrderInitializationAllowed;

    /**
     * @var IsRedirectToBoldCheckoutAllowedInterface
     */
    private $isRedirectToBoldCheckoutAllowed;

    /**
     * @param Session $session
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param ClientInterface $client
     * @param IsOrderInitializationAllowedInterface $isOrderInitializationAllowed
     * @param IsRedirectToBoldCheckoutAllowedInterface $isRedirectToBoldCheckoutAllowed
     */
    public function __construct(
        Session $session,
        InitOrderFromQuote $initOrderFromQuote,
        ConfigInterface $config,
        LoggerInterface $logger,
        ClientInterface $client,
        IsOrderInitializationAllowedInterface $isOrderInitializationAllowed,
        IsRedirectToBoldCheckoutAllowedInterface $isRedirectToBoldCheckoutAllowed
    ) {
        $this->session = $session;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
        $this->isOrderInitializationAllowed = $isOrderInitializationAllowed;
        $this->isRedirectToBoldCheckoutAllowed = $isRedirectToBoldCheckoutAllowed;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $this->session->getQuote();
        $request = $observer->getRequest();
        $this->session->setBoldCheckoutData(null);
        if (!$this->isOrderInitializationAllowed->isAllowed($quote, $request)) {
            return;
        }
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            $this->session->setBoldCheckoutData($checkoutData);
            if (!$this->isRedirectToBoldCheckoutAllowed->isAllowed($quote, $request)) {
                return;
            }
            $this->client->get($websiteId, 'refresh');
            $orderId = $checkoutData['data']['public_order_id'];
            $token = $checkoutData['data']['jwt_token'];
            $shopName = $checkoutData['data']['initial_data']['shop_name'];
            $checkoutApiUrl = rtrim($this->config->getCheckoutUrl($websiteId), '/') . '/bold_platform/';
            $checkoutUrl = $checkoutApiUrl . $shopName . '/experience/resume?public_order_id=' . $orderId
                . '&token=' . $token;
            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}

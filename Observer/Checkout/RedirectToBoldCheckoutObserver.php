<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\IsBoldCheckoutAllowedForRequest;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
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
     * @var IsBoldCheckoutAllowedForCart
     */
    private $allowedForCart;

    /**
     * @var IsBoldCheckoutAllowedForRequest
     */
    private $allowedForRequest;

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
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param IsBoldCheckoutAllowedForRequest $allowedForRequest
     * @param Session $session
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param ClientInterface $client
     */
    public function __construct(
        IsBoldCheckoutAllowedForCart $allowedForCart,
        IsBoldCheckoutAllowedForRequest $allowedForRequest,
        Session $session,
        InitOrderFromQuote $initOrderFromQuote,
        ConfigInterface $config,
        LoggerInterface $logger,
        ClientInterface $client
    ) {
        $this->allowedForCart = $allowedForCart;
        $this->allowedForRequest = $allowedForRequest;
        $this->session = $session;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        file_put_contents('/home/s3jamaligarden/public_html/var/log/ddd.log', '_1_');
        $quote = $this->session->getQuote();
        $request = $observer->getRequest();
        $this->session->setBoldCheckoutData(null);
        if (!$this->allowedForCart->isAllowed($quote)) {
            file_put_contents('/home/s3jamaligarden/public_html/var/log/ddd.log', '_2_');
            return;
        }
        if (!$this->allowedForRequest->isAllowed($quote, $request)) {
            file_put_contents('/home/s3jamaligarden/public_html/var/log/ddd.log', '_3_');
            return;
        }
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            $this->session->setBoldCheckoutData($checkoutData);
            if ($this->config->isCheckoutTypeSelfHosted($websiteId)) {
                file_put_contents('/home/s3jamaligarden/public_html/var/log/ddd.log', '_4_');
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
            file_put_contents('/home/s3jamaligarden/public_html/var/log/ddd.log', '_5_');
            $this->logger->critical($exception);
        }
    }
}

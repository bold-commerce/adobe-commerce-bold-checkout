<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\IsBoldCheckoutAllowedForRequest;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
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
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param IsBoldCheckoutAllowedForRequest $allowedForRequest
     * @param Session $session
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        IsBoldCheckoutAllowedForCart    $allowedForCart,
        IsBoldCheckoutAllowedForRequest $allowedForRequest,
        Session                         $session,
        InitOrderFromQuote              $initOrderFromQuote,
        ConfigInterface                 $config,
        LoggerInterface                 $logger
    ) {
        $this->allowedForCart = $allowedForCart;
        $this->allowedForRequest = $allowedForRequest;
        $this->session = $session;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $this->session->getQuote();
        $request = $observer->getRequest();
        $this->session->setBoldCheckoutData(null);
        if (!$this->allowedForCart->isAllowed($quote)) {
            return;
        }
        if (!$this->allowedForRequest->isAllowed($quote, $request)) {
            return;
        }
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            if ($this->config->isCheckoutTypeSelfHosted($websiteId)) {
                $this->session->setBoldCheckoutData($checkoutData);
                return;
            }
            $orderId = $checkoutData['data']['public_order_id'];
            $token = $checkoutData['data']['jwt_token'];
            $shopName = $checkoutData['data']['initial_data']['shop_name'];
            $checkoutApiUrl = rtrim($this->config->getCheckoutUrl($websiteId), '/') . '/bold_platform/';
            $checkoutUrl = $checkoutApiUrl . $shopName . '/experience/resume?public_order_id=' . $orderId
                . '&token=' . $token;

            $ga = $request->getCookie('_ga');
            if(!empty($ga)) {
                $ga = explode('.', $ga, 3);
                $_ga = array_pop($ga);
                $checkoutUrl = $checkoutUrl . '&_ga=' . $_ga;
            }
            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
    }
}

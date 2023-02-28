<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
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
     * @var Session
     */
    private $session;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InitOrderFromQuote
     */
    private $initOrderFromQuote;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param ConfigInterface $config
     * @param Session $session
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param InitOrderFromQuote $initOrderFromQuote
     */
    public function __construct(
        IsBoldCheckoutAllowedForCart $allowedForCart,
        ConfigInterface $config,
        Session $session,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        InitOrderFromQuote $initOrderFromQuote
    ) {
        $this->allowedForCart = $allowedForCart;
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $this->session->getQuote();
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        if (!$this->allowedForCart->isAllowed($quote)) {
            return;
        }
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            $orderId = $checkoutData['data']['public_order_id'];
            $token = $checkoutData['data']['jwt_token'];
            $shipName = $checkoutData['data']['initial_data']['shop_name'];
            $checkoutUrl = $this->config->getCheckoutUrl($websiteId);
            $checkoutUrl .= '/bold_platform/' . $shipName . '/experience/resume?public_order_id='
                . $orderId . '&token=' . $token;
            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('There was an error during checkout. Please contact us or try again later.')
            );
            $this->logger->error('Bold Checkout error: ' . $exception->getMessage());
            $observer->getControllerAction()->getResponse()->setRedirect('/');
        }
    }
}

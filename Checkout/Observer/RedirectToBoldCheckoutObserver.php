<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Redirect to bold checkout if allowed.
 */
class RedirectToBoldCheckoutObserver implements ObserverInterface
{
    private const CHECKOUT_URL = 'https://checkout.boldcommerce.com/bold_platform/';

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
     * @var InitOrderFromQuote
     */
    private $initOrderFromQuote;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param Session $session
     * @param ManagerInterface $messageManager
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param Session $session
     */
    public function __construct(
        IsBoldCheckoutAllowedForCart $allowedForCart,
        Session $session,
        ManagerInterface $messageManager,
        InitOrderFromQuote $initOrderFromQuote,
        ConfigInterface $config
    ) {
        $this->allowedForCart = $allowedForCart;
        $this->session = $session;
        $this->messageManager = $messageManager;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $this->session->getQuote();
        if (!$this->allowedForCart->isAllowed($quote)) {
            return;
        }
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            if ($this->config->isSelfHostedCheckoutEnabled((int)$quote->getStore()->getWebsiteId())) {
                $this->session->setBoldCheckoutData($checkoutData);
                return;
            }
            $orderId = $checkoutData['data']['public_order_id'];
            $token = $checkoutData['data']['jwt_token'];
            $shopName = $checkoutData['data']['initial_data']['shop_name'];
            $checkoutUrl = self::CHECKOUT_URL . $shopName . '/experience/resume?public_order_id=' . $orderId
                . '&token=' . $token;
            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            if ($this->config->isSelfHostedCheckoutEnabled((int)$quote->getStore()->getWebsiteId())) {
                return;
            }
            $this->messageManager->addErrorMessage(
                __('There was an error during checkout. Please contact us or try again later.')
            );
            $observer->getControllerAction()->getResponse()->setRedirect('/');
        }
    }
}

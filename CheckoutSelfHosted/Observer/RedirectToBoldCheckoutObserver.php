<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Observer;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\IsBoldCheckoutAllowedForRequest;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Bold\Checkout\Observer\Checkout\RedirectToBoldCheckoutObserver as RedirectToBoldCheckout;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Redirect to Self-hosted Bold Checkout Observer.
 */
class RedirectToBoldCheckoutObserver implements ObserverInterface
{
    /**
     * @var RedirectToBoldCheckout
     */
    private $redirectToBoldCheckoutObserver;

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
    private $checkoutSession;

    /**
     * @var InitOrderFromQuote
     */
    private $initOrderFromQuote;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RedirectToBoldCheckout $redirectToBoldCheckoutObserver
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     * @param IsBoldCheckoutAllowedForRequest $allowedForRequest
     * @param Session $checkoutSession
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RedirectToBoldCheckout $redirectToBoldCheckoutObserver,
        IsBoldCheckoutAllowedForCart $allowedForCart,
        IsBoldCheckoutAllowedForRequest $allowedForRequest,
        Session $checkoutSession,
        InitOrderFromQuote $initOrderFromQuote,
        ConfigInterface $config,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager
    ) {
        $this->redirectToBoldCheckoutObserver = $redirectToBoldCheckoutObserver;
        $this->allowedForCart = $allowedForCart;
        $this->allowedForRequest = $allowedForRequest;
        $this->checkoutSession = $checkoutSession;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Redirect to self-hosted Bold Checkout.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$this->config->isCheckoutTypeSelfHosted((int)$quote->getStore()->getWebsiteId())) {
            $this->redirectToBoldCheckoutObserver->execute($observer);
            return;
        }
        $request = $observer->getRequest();
        if (!$this->allowedForCart->isAllowed($quote)) {
            return;
        }
        if (!$this->allowedForRequest->isAllowed($quote, $request)) {
            return;
        }
        try {
            $checkoutData = $this->initOrderFromQuote->init($quote);
            $this->checkoutSession->setBoldCheckoutData($checkoutData);
            $checkoutUrl = $this->storeManager->getStore()->getUrl('experience/index/index');
            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage(
                __('There was an error during checkout. Please contact us or try again later.')
            );
            $observer->getControllerAction()->getResponse()->setRedirect('/');
        } finally {
            $observer->getControllerAction()->getActionFlag()->set(
                '',
                ActionInterface::FLAG_NO_DISPATCH,
                true
            );
        }
    }
}

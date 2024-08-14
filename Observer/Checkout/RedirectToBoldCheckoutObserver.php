<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Checkout;

use Bold\Checkout\Api\BoldQuoteRepositoryInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Order\InitOrderFromQuote;
use Bold\Checkout\Model\Order\ResumeOrder;
use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\RedirectToBoldCheckout\IsOrderInitializationAllowedInterface;
use Bold\Checkout\Model\RedirectToBoldCheckout\IsRedirectToBoldCheckoutAllowedInterface;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

/**
 * Redirect to Bold checkout if allowed.
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
    private $isOrderInitializationAllowed;

    /**
     * @var IsRedirectToBoldCheckoutAllowedInterface
     */
    private $isRedirectToBoldCheckoutAllowed;

    /**
     * @var BoldQuoteRepositoryInterface
     */
    private $boldQuoteRepository;

    /**
     * @var ResumeOrder
     */
    private $resumeOrder;

    /**
     * @param Session $session
     * @param InitOrderFromQuote $initOrderFromQuote
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param ClientInterface $client
     * @param IsOrderInitializationAllowedInterface $isOrderInitializationAllowed
     * @param IsRedirectToBoldCheckoutAllowedInterface $isRedirectToBoldCheckoutAllowed
     * @param BoldQuoteRepositoryInterface $boldQuoteRepository
     * @param ResumeOrder $resumeOrder
     */
    public function __construct(
        Session $session,
        InitOrderFromQuote $initOrderFromQuote,
        ConfigInterface $config,
        LoggerInterface $logger,
        ClientInterface $client,
        IsOrderInitializationAllowedInterface $isOrderInitializationAllowed,
        IsRedirectToBoldCheckoutAllowedInterface $isRedirectToBoldCheckoutAllowed,
        BoldQuoteRepositoryInterface $boldQuoteRepository,
        ResumeOrder $resumeOrder
    ) {
        $this->session = $session;
        $this->initOrderFromQuote = $initOrderFromQuote;
        $this->config = $config;
        $this->logger = $logger;
        $this->client = $client;
        $this->isOrderInitializationAllowed = $isOrderInitializationAllowed;
        $this->isRedirectToBoldCheckoutAllowed = $isRedirectToBoldCheckoutAllowed;
        $this->boldQuoteRepository = $boldQuoteRepository;
        $this->resumeOrder = $resumeOrder;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        $quote = $this->session->getQuote();
        $request = $observer->getRequest();
        $this->session->setBoldCheckoutData(null);
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        try {
            if (!$this->isOrderInitializationAllowed->isAllowed($quote, $request)) {
                return;
            }

            $checkoutData = $this->resumeExistingCart($quote);
            if ($checkoutData === null) {
                $checkoutData = $this->initOrderFromQuote->init($quote);
            }

            $this->session->setBoldCheckoutData($checkoutData);
            if (!$this->isRedirectToBoldCheckoutAllowed->isAllowed($quote, $request)) {
                return;
            }

            $this->client->get($websiteId, 'refresh');

            $orderId = $checkoutData['data']['public_order_id'] ?? '';
            $token = $checkoutData['data']['jwt_token'] ?? '';
            $shopName = $checkoutData['data']['initial_data']['shop_name'] ?? '';

            $checkoutUrl = $this->getCheckoutUrl(
                $websiteId,
                $shopName,
                $orderId,
                $token
            );

            $observer->getControllerAction()->getResponse()->setRedirect($checkoutUrl);
        } catch (Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    private function getPublicOrderIdForCart(int $cartId): string
    {
        return $this->boldQuoteRepository
            ->getByCartId($cartId)
            ->getPublicOrderId() ?? '';
    }

    private function resumeExistingCart(CartInterface $cart): array|null
    {
        try {
            $publicOrderId = $this->getPublicOrderIdForCart((int) $cart->getId());
            return $this->resumeOrder->resume($cart, $publicOrderId);
        } catch (NoSuchEntityException|LocalizedException) {
            return null;
        }
    }

    private function getCheckoutUrl(int $websiteId, string $shopName, string $publicOrderId, string $token): string
    {
        return \sprintf(
            '%s/bold_platform/%s/experience/resume?public_order_id=%s&jwt_token=%s',
            rtrim($this->config->getCheckoutUrl($websiteId), '/'),
            $shopName,
            $publicOrderId,
            $token
        );
    }
}

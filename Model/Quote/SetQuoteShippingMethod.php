<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteShippingMethodInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Set quote shipping method service.
 */
class SetQuoteShippingMethod implements SetQuoteShippingMethodInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $quoteResultBuilder;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var ShippingInformationManagementInterface
     */
    private $shippingInformationManagement;

    /**
     * @var ShippingInformationInterfaceFactory
     */
    private $shippingInformationFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ShippingInformationManagementInterface $shippingInformationManagement
     * @param ShippingInformationInterfaceFactory $shippingInformationFactory
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param Builder $quoteResultBuilder
     * @param ConfigInterface $config
     * @param Cart $cart used for the backward compatibility with earlier versions of Magento.
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     */
    public function __construct(
        ShippingInformationManagementInterface $shippingInformationManagement,
        ShippingInformationInterfaceFactory $shippingInformationFactory,
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        Builder $quoteResultBuilder,
        ConfigInterface $config,
        Cart $cart,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformationFactory = $shippingInformationFactory;
        $this->cartRepository = $cartRepository;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->shopIdValidator = $shopIdValidator;
        $this->config = $config;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(
        string $shopId,
        int $cartId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): ResultInterface {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->checkoutSession->replaceQuote($quote);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        if ($this->config->isCheckoutTypeSelfHosted((int)$quote->getStore()->getWebsiteId())) {
            $this->quoteResultBuilder->createSuccessResult($quote);
        }
        $this->cart->setQuote($quote);
        $shippingInformation = $this->shippingInformationFactory->create()
            ->setShippingAddress($quote->getShippingAddress())
            ->setBillingAddress($quote->getBillingAddress())
            ->setShippingCarrierCode($shippingCarrierCode)
            ->setShippingMethodCode($shippingMethodCode);
        $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingInformation);
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}

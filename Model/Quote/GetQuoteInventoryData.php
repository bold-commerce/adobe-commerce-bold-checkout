<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterfaceFactory;
use Bold\Checkout\Api\Quote\GetQuoteInventoryDataInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Exception;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

/**
 * Get quote items inventory data.
 */
class GetQuoteInventoryData implements GetQuoteInventoryDataInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var InventoryDataInterfaceFactory
     */
    private $inventoryDataFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ObjectManagerInterface $objectManager
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param InventoryDataInterfaceFactory $inventoryDataFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param Session $checkoutSession
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ObjectManagerInterface $objectManager,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        InventoryDataInterfaceFactory $inventoryDataFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        Session $checkoutSession
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->inventoryDataFactory = $inventoryDataFactory;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function getInventory(string $shopId, int $cartId): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->checkoutSession->replaceQuote($quote);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (LocalizedException $e) {
            return $this->buildErrorResponse($e->getMessage());
        }
        $inventoryResult = [];
        foreach ($quote->getAllItems() as $item) {
            if (!GetCartLineItems::shouldAppearInCart($item)) {
                continue;
            }
            $inventoryResult[] = $this->inventoryDataFactory->create(
                [
                    'cartItemId' => $item->getId(),
                    'isSalable' => $this->isProductSalable($item),
                ]
            );
        }
        return $this->resultFactory->create(
            [
                'inventoryData' => $inventoryResult,
            ]
        );
    }

    /**
     * Build validation error response.
     *
     * @param string $error
     * @return ResultInterface
     */
    private function buildErrorResponse(string $error): ResultInterface
    {
        return $this->resultFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $error,
                            'code' => 422,
                            'type' => 'server.validation_error',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Get product salable status.
     *
     * @param CartItemInterface $item
     * @return bool
     */
    private function isProductSalable(CartItemInterface $item): bool
    {
        // If the product is a bundle type, get the salabilty of all it's children instead
        if ($item->getProductType() === Bundle::TYPE_CODE) {
            foreach ($item->getChildren() as $childItem) {
                if (!$this->isProductSalable($childItem)) {
                    return false;
                }
            }

            return true;
        }

        try {
            if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
                return (bool)$item->getProduct()->isSalable();
            }
            $stockResolver = $this->getStockResolverService();
            $isProductSalableForRequestedQty = $this->getIsProductSalableForRequestedQtyService();
            if (!$stockResolver || !$isProductSalableForRequestedQty) {
                return (bool)$item->getProduct()->isSalable();
            }
            $sku = $item['product']['sku'];
            $requestedQty = $item->getParentItem() ? (float)$item->getParentItem()->getQty() : (float)$item->getQty();
            $websiteId = (int)$this->storeManager->getStore($item->getStoreId())->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $stockId = $stockResolver->execute('website', $websiteCode)->getStockId();
            $result = $isProductSalableForRequestedQty->execute($sku, $stockId, $requestedQty);
            return $result->isSalable();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Try to build \Magento\InventorySalesApi\Api\StockResolverInterface. If it's not possible, return null.
     *
     * @return StockResolverInterface|null
     */
    private function getStockResolverService(): ?StockResolverInterface
    {
        try {
            $stockResolver = $this->objectManager->get(StockResolverInterface::class);
        } catch (Throwable $e) {
            $stockResolver = null;
        }
        return $stockResolver;
    }

    /**
     * Try to build \Magento\InventorySalesApi\Api\IsProductSalableForRequestedQtyInterface. If it's not possible,
     * return null.
     *
     * @return IsProductSalableForRequestedQtyInterface|null
     */
    private function getIsProductSalableForRequestedQtyService(): ?IsProductSalableForRequestedQtyInterface
    {
        try {
            $isProductSalableForRequestedQty = $this->objectManager->get(
                IsProductSalableForRequestedQtyInterface::class
            );
        } catch (Throwable $e) {
            $isProductSalableForRequestedQty = null;
        }
        return $isProductSalableForRequestedQty;
    }
}

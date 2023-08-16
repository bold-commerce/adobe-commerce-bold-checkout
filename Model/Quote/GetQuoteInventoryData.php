<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterfaceFactory;
use Magento\Bundle\Model\Product\Type as Bundle;
use Bold\Checkout\Api\Quote\GetQuoteInventoryDataInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
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
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ObjectManagerInterface $objectManager
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param InventoryDataInterfaceFactory $inventoryDataFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ObjectManagerInterface $objectManager,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        InventoryDataInterfaceFactory $inventoryDataFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->inventoryDataFactory = $inventoryDataFactory;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function getInventory(string $shopId, int $cartId): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
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
            $stockResolver = $this->getStockResolverService();
            $productSalableForRequestedQtyService = $this->getProductSalableForRequestedQtyService();
            $sku = $item['product']['sku'];
            $requestedQty = $item->getParentItem() ? (float)$item->getParentItem()->getQty() : (float)$item->getQty();
            $request = $this->getRequest(
                (string)$sku,
                $requestedQty
            );
            if (!$request || !$stockResolver || !$productSalableForRequestedQtyService) {
                return (bool)$item->getProduct()->isSalable();
            }
            $websiteId = (int)$this->storeManager->getStore($item->getStoreId())->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
            $stockId = $stockResolver->execute('website', $websiteCode)->getStockId();
            $result = current($productSalableForRequestedQtyService->execute([$request], $stockId));
            return $result->isSalable();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Try to build \Magento\InventorySalesApi\Api\AreProductsSalableForRequestedQtyInterface. If it's not possible, return null.
     *
     * @return \Magento\InventorySalesApi\Api\AreProductsSalableForRequestedQtyInterface|null
     */
    private function getProductSalableForRequestedQtyService(): ?\Magento\InventorySalesApi\Api\AreProductsSalableForRequestedQtyInterface
    {
        try {
            $areProductsSalable = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\AreProductsSalableForRequestedQtyInterface::class
            );
        } catch (Throwable $e) {
            $areProductsSalable = null;
        }
        return $areProductsSalable;
    }

    /**
     * Try to build \Magento\InventorySalesApi\Api\StockResolverInterface. If it's not possible, return null.
     *
     * @return \Magento\InventorySalesApi\Api\StockResolverInterface|null
     */
    private function getStockResolverService(): ?\Magento\InventorySalesApi\Api\StockResolverInterface
    {
        try {
            $stockResolver = $this->objectManager->get(\Magento\InventorySalesApi\Api\StockResolverInterface::class);
        } catch (Throwable $e) {
            $stockResolver = null;
        }
        return $stockResolver;
    }

    /**
     * Try to build \Magento\InventorySalesApi\Api\Data\IsProductSalableForRequestedQtyRequestInterface.
     * If it's not possible, return null.
     *
     * @param string $sku
     * @param float $qty
     * @return \Magento\InventorySalesApi\Api\Data\IsProductSalableForRequestedQtyRequestInterface|null
     */
    private function getRequest(
        string $sku,
        float $qty
    ): ?\Magento\InventorySalesApi\Api\Data\IsProductSalableForRequestedQtyRequestInterface {
        try {
            $requestFactory = $this->objectManager->get(
                \Magento\InventorySalesApi\Api\Data\IsProductSalableForRequestedQtyRequestInterfaceFactory::class
            );
            $request = $requestFactory->create(
                [
                    'sku' => $sku,
                    'qty' => $qty,
                ]
            );
        } catch (Throwable $e) {
            $request = null;
        }
        return $request;
    }
}

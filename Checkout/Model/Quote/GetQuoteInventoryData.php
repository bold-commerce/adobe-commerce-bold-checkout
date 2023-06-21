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
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
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
            if ($item->getChildren()) {
                continue;
            }
            $inventoryResult[] = $this->inventoryDataFactory->create(
                [
                    'cartItemId' => $item->getId(),
                    'salableQty' => $this->getSalableQty($item),
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
     * Get salable qty for the item.
     *
     * @param CartItemInterface $item
     * @return float
     */
    private function getSalableQty(CartItemInterface $item): float
    {
        $getProductSalableQty = $this->getProductSalableQtyService();
        $stockResolver = $this->getStockResolverService();
        try {
            return ($getProductSalableQty && $stockResolver)
                ? $this->getSalableQuantity($getProductSalableQty, $stockResolver, $item)
                : $item->getProduct()->getExtensionAttributes()->getStockItem()->getQty();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get product salable qty.
     *
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockResolverInterface $stockResolver
     * @param CartItemInterface $item
     * @return float
     * @throws LocalizedException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function getSalableQuantity(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockResolverInterface        $stockResolver,
        CartItemInterface             $item
    ): float {
        $websiteId = (int)$this->storeManager->getStore($item->getStoreId())->getWebsiteId();
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        $stockId = $stockResolver->execute('website', $websiteCode)->getStockId();

        return $getProductSalableQty->execute($item['product']['sku'], $stockId);
    }

    /**
     * Try to build GetProductSalableQtyInterface. If it's not possible, return null.
     *
     * @return GetProductSalableQtyInterface|null
     */
    public function getProductSalableQtyService(): ?GetProductSalableQtyInterface
    {
        try {
            $getProductSalableQty = $this->objectManager->get(GetProductSalableQtyInterface::class);
        } catch (Throwable $e) {
            $getProductSalableQty = null;
        }
        return $getProductSalableQty;
    }

    /**
     * Try to build StockResolverInterface. If it's not possible, return null.
     *
     * @return StockResolverInterface|null
     */
    public function getStockResolverService(): ?StockResolverInterface
    {
        try {
            $stockResolver = $this->objectManager->get(StockResolverInterface::class);
        } catch (Throwable $e) {
            $stockResolver = null;
        }
        return $stockResolver;
    }
}

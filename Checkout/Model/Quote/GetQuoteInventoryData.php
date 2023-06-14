<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface;
use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterfaceFactory;
use Bold\Checkout\Api\Quote\GetQuoteInventoryDataInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

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
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param InventoryDataInterfaceFactory $inventoryDataFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        InventoryDataInterfaceFactory $inventoryDataFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->inventoryDataFactory = $inventoryDataFactory;
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
            if ($item->getShildren()) {
                continue;
            }
            $inventoryResult[] = $this->inventoryDataFactory->create(
                [
                    'cartItemId' => $item->getId(),
                    'salableQty' => $item->getProduct()->getExtensionAttributes()->getStockItem()->getQty(),
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
}

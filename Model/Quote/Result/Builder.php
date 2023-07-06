<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Data\Quote\ResultInterfaceFactory;
use Bold\Checkout\Model\Quote\Result\Builder\ExtractCartTotals;
use Bold\Checkout\Model\Quote\Result\Builder\ExtractShippingMethods;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Quote result builder.
 */
class Builder
{
    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var ExtractShippingMethods
     */
    private $extractShippingMethods;

    /**
     * @var ExtractCartTotals
     */
    private $extractCartTotals;

    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    private $mediaGalleryManagement;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ExtractShippingMethods $extractShippingMethods
     * @param ExtractCartTotals $extractCartTotals
     * @param ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        ExtractShippingMethods $extractShippingMethods,
        ExtractCartTotals $extractCartTotals,
        ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement
    ) {
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->extractShippingMethods = $extractShippingMethods;
        $this->extractCartTotals = $extractCartTotals;
        $this->mediaGalleryManagement = $mediaGalleryManagement;
    }

    /**
     * Build quote result.
     *
     * @param CartInterface $quote
     * @return ResultInterface
     */
    public function createSuccessResult(CartInterface $quote): ResultInterface
    {
        $this->processQuoteItems($quote);
        return $this->resultFactory->create(
            [
                'quote' => $quote,
                'totals' => $this->extractCartTotals->extract($quote),
                'shippingMethods' => $this->extractShippingMethods->extract($quote),
            ]
        );
    }

    /**
     * Create quote error result.
     *
     * @param string $error
     * @param int $code
     * @param string $type
     * @return ResultInterface
     */
    public function createErrorResult(
        string $error,
        int $code = 422,
        string $type = 'server.validation_error'
    ): ResultInterface {
        return $this->resultFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $error,
                            'code' => $code,
                            'type' => $type,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Add product to quote items extension attributes.
     *
     * This is needed for Bold Checkout to be able to display product information in the cart.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function processQuoteItems(CartInterface $quote): void
    {
        $items = [];
        foreach ($quote->getAllItems() as $item) {
            if ($item->getChildren()) {
                continue;
            }
            $parentProduct = null;
            if ($item->getParentItem() !== null) {
                $parentItem = $item->getParentItem();
                $item->getExtensionAttributes()->setParentItemId($parentItem->getId());
                $item->getExtensionAttributes()->setTaxDetails($parentItem->getExtensionAttributes()->getTaxDetails());
                $parentProduct = $parentItem->getProduct();
            }
            $product = $item->getProduct();
            $product = $product->load($product->getEntityId());
            $mediaGallery = $this->mediaGalleryManagement->getList($product['sku']);
            if (!$mediaGallery && $parentProduct) {
                $mediaGallery = $this->mediaGalleryManagement->getList($parentProduct['sku']);
                $product->setMediaGalleryEntries($mediaGallery);
            }
            $item->getExtensionAttributes()->setProduct($product);
            $items[] = $item;
        }
        $quote->setItems($items);
    }
}

<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Data\Quote\ResultInterfaceFactory;
use Bold\Checkout\Model\Quote\Result\Builder\ExtractCartTotals;
use Bold\Checkout\Model\Quote\Result\Builder\ExtractShippingMethods;
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
     * @param ResultInterfaceFactory $resultFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ExtractShippingMethods $extractShippingMethods
     * @param ExtractCartTotals $extractCartTotals
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ErrorInterfaceFactory $errorFactory,
        ExtractShippingMethods $extractShippingMethods,
        ExtractCartTotals $extractCartTotals
    ) {
        $this->resultFactory = $resultFactory;
        $this->errorFactory = $errorFactory;
        $this->extractShippingMethods = $extractShippingMethods;
        $this->extractCartTotals = $extractCartTotals;
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
     * Add product to quote items extension attributes.
     *
     * This is needed for Bold Checkout to be able to display product information in the cart.
     *
     * @param CartInterface $quote
     * @return void
     */
    private function processQuoteItems(CartInterface $quote): void
    {
        $quoteItems = $quote->getAllVisibleItems();
        foreach ($quoteItems as $quoteItem) {
            $quoteItem->getExtensionAttributes()->setProduct($quoteItem->getProduct());
        }
    }

    /**
     * Create quote error result.
     *
     * @param string $error
     * @return ResultInterface
     */
    public function createErrorResult(string $error): ResultInterface
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

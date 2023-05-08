<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\SetQuoteAddresses;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\Data\TotalsInterfaceFactory;
use Magento\Quote\Model\Cart\Totals\ItemConverter;
use Magento\Quote\Model\Cart\TotalsConverter;

/**
 * Cart totals extractor.
 */
class ExtractCartTotals
{
    /**
     * @var TotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ItemConverter
     */
    private $itemConverter;

    /**
     * @var CouponManagementInterface
     */
    private $couponService;

    /**
     * @var TotalsConverter
     */
    private $totalsConverter;

    /**
     * @param TotalsInterfaceFactory $totalsFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param ItemConverter $itemConverter
     * @param CouponManagementInterface $couponService
     * @param TotalsConverter $totalsConverter
     */
    public function __construct(
        TotalsInterfaceFactory $totalsFactory,
        CartRepositoryInterface $quoteRepository,
        DataObjectHelper $dataObjectHelper,
        ItemConverter $itemConverter,
        CouponManagementInterface $couponService,
        TotalsConverter $totalsConverter
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->itemConverter = $itemConverter;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
    }

    /**
     * Extract cart totals.
     *
     * @param CartInterface $quote
     * @return TotalsInterface
     */
    public function extract(CartInterface $quote): TotalsInterface
    {
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $addressTotals = $address->getTotals();
        $addressTotalsData = $address->getData();
        unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        $quoteTotals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            TotalsInterface::class
        );
        $items = array_map([$this->itemConverter, 'modelToDataObject'], $quote->getAllVisibleItems());
        $calculatedTotals = $this->totalsConverter->process($addressTotals);
        $quoteTotals->setTotalSegments($calculatedTotals);
        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = max($amount, 0);
        try {
            $couponCode = $this->couponService->get($quote->getId());
        } catch (NoSuchEntityException $e) {
            $couponCode = '';
        }
        $quoteTotals->setCouponCode($couponCode);
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItems($items);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());
        return $quoteTotals;
    }
}

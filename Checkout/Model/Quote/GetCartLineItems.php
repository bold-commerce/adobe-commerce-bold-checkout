<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Cart line items builder.
 */
class GetCartLineItems
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Configuration $configuration
     * @param Configurable $configurableType
     * @param Escaper $escaper
     */
    public function __construct(Configuration $configuration, Configurable $configurableType, Escaper $escaper)
    {
        $this->configuration = $configuration;
        $this->configurableType = $configurableType;
        $this->escaper = $escaper;
    }

    /**
     * Extract line items data.
     *
     * @param CartInterface $quote
     * @return array
     * @throws LocalizedException
     */
    public function getItems(CartInterface $quote): array
    {
        $lineItems = [];
        foreach ($quote->getAllItems() as $cartItem) {
            if (!$cartItem->getChildren()) {
                $lineItems[] = $this->getLineItem($cartItem);
            }
        }
        if (!$lineItems) {
            throw new LocalizedException(__('There are no cart items to checkout.'));
        }

        return $lineItems;
    }

    /**
     * Extract quote item entity data into array.
     *
     * @param CartItemInterface $item
     * @return array
     */
    private function getLineItem(CartItemInterface $item): array
    {
        $lineItem = [
            'platform_id' => (string)$item->getProduct()->getId(),
            'quantity' => $this->extractLineItemQuantity($item),
            'line_item_key' => (string)$item->getId(),
            'price_adjustment' => $this->getPriceAdjustment($item),
            'line_item_properties' => [
                '_quote_id' => (string)$item->getQuoteId(),
                '_store_id' => (string)$item->getQuote()->getStoreId(),
            ],
        ];
        $item = $item->getParentItem() ?: $item;
        if ($item->getProductType() === Configurable::TYPE_CODE) {
            $lineItem = $this->addConfigurableOptions($item, $lineItem);
        }
        foreach ($this->configuration->getCustomOptions($item) as $customOption) {
            $lineItem = $this->addCustomOptions($customOption, $lineItem);
        }
        return $lineItem;
    }

    /**
     * Get quote item quantity considering product type.
     *
     * @param CartItemInterface $item
     * @return int
     */
    private function extractLineItemQuantity(CartItemInterface $item): int
    {
        $parentItem = $item->getParentItem();
        if ($parentItem) {
            $item = $parentItem;
        }

        return (int)$item->getQty();
    }

    /**
     * Get quote item discount amount.
     *
     * @param CartItemInterface $item
     * @return float
     */
    private static function getPriceAdjustment(CartItemInterface $item)
    {
        $parentItem = $item->getParentItem();
        $childProduct = $item->getProduct();
        $baseProductPrice = $childProduct->getPrice();
        if ($parentItem) {
            $item = $parentItem;
        }
        $priceAdjustment = $item->getBasePrice() - $baseProductPrice;

        return $priceAdjustment * 100;
    }

    /**
     * Add cart item configuration options to line item.
     *
     * @param CartItemInterface $item
     * @param array $lineItem
     * @return array
     */
    public function addConfigurableOptions(CartItemInterface $item, array $lineItem): array
    {
        foreach ($this->configurableType->getOrderOptions($item->getProduct())['attributes_info'] as $option) {
            $label = $this->escaper->escapeHtml($option['label']);
            $value = $this->escaper->escapeHtml($option['value']);
            $lineItem['line_item_properties'][$label] = $value;
        }
        return $lineItem;
    }

    /**
     * Add product custom options to line item.
     *
     * @param array $customOption
     * @param array $lineItem
     * @return array
     */
    public function addCustomOptions(array $customOption, array $lineItem): array
    {
        $label = $this->escaper->escapeHtml($customOption['label']);
        $value = $this->configuration->getFormattedOptionValue(
            $customOption,
            [
                ['max_length' => 55],
            ]
        );
        $lineItem['line_item_properties'][$label] = $value['value'] ?? '';
        return $lineItem;
    }
}

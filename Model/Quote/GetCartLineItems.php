<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product\Type as Virtual;
use Magento\Downloadable\Model\Product\Type as Downloadable;

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var UrlBuilder
     */
    private $productUrlBuilder;

    /**
     * @var Bundle
     */
    private $bundleType;

    /**
     * @param Configuration $configuration
     * @param Configurable $configurableType
     * @param Escaper $escaper
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param UrlBuilder $productUrlBuilder
     * @param Bundle $bundleType
     */
    public function __construct(
        Configuration $configuration,
        Configurable $configurableType,
        Escaper $escaper,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository,
        UrlBuilder $productUrlBuilder,
        Type $bundleType
    ) {
        $this->configuration = $configuration;
        $this->configurableType = $configurableType;
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->productUrlBuilder = $productUrlBuilder;
        $this->bundleType = $bundleType;
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
        /** @var CartItemInterface $cartItem */
        foreach ($quote->getAllItems() as $cartItem) {
            if (static::shouldAppearInCart($cartItem)) {
                $lineItems[] = $this->getLineItem($cartItem);
            }
        }

        if (!$lineItems) {
            throw new LocalizedException(__('There are no cart items to checkout.'));
        }
        return $lineItems;
    }

    /**
     * Determines if the cart item should appear in the cart sent to Bold
     *
     * @param Item $item
     * @return boolean
     */
    public static function shouldAppearInCart(CartItemInterface $item): bool
    {
        $parentItem = $item->getParentItem();
        $parentIsBundle = $parentItem && $parentItem->getProductType() === Bundle::TYPE_CODE;
        return (!$item->getChildren() && !$parentIsBundle) || $item->getProductType() === Bundle::TYPE_CODE;
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
            'id' => (int)$item->getProduct()->getId(),
            'quantity' => $this->extractLineItemQuantity($item),
            'title' => $this->getLineItemName($item),
            'product_title' => $this->getLineItemName($item),
            'weight' => $this->getLineItemWeightInGrams($item),
            'taxable' => true, // Doesn't matter since RSA will handle taxes
            'image' => $this->getLineItemImage($item),
            'requires_shipping' => $this->getRequiresShipping($item),
            'line_item_key' => (string)$item->getId(),
            'price' => $this->getLineItemPrice($item),
        ];

        $item = $item->getParentItem() ?: $item;
        if ($item->getProductType() === Configurable::TYPE_CODE) {
            $lineItem = $this->addConfigurableOptions($item, $lineItem);
        }
        if ($item->getProductType() === Bundle::TYPE_CODE) {
            $lineItem = $this->addBundleOptions($item, $lineItem);
        }
        foreach ($this->configuration->getCustomOptions($item) as $customOption) {
            $lineItem = $this->addCustomOptions($customOption, $lineItem);
        }
        return $lineItem;
    }

    /**
     * Gets the product's name from the line item
     *
     * @param CartItemInterface $item
     * @return string
     */
    private function getLineItemName(CartItemInterface $item): string
    {
        $item = $item->getParentItem() ?: $item;
        return $item->getName();
    }

    /**
     * Gets the price of a line item
     *
     * @param CartItemInterface $item
     * @return int
     */
    private function getLineItemPrice(CartItemInterface $item)
    {
        $item = $item->getParentItem() ?: $item;
        return $this->convertToCents((float)$item->getPrice());
    }

    /**
     * Gets the weight of a line item in grams
     *
     * @param CartItemInterface $item
     * @return float
     */
    private function getLineItemWeightInGrams(CartItemInterface $item): float
    {
        $unit = strtolower(
            $this->scopeConfig->getValue('general/locale/weight_unit', ScopeInterface::SCOPE_STORE)
        );
        $weight = $item->getWeight();
        if ($unit === 'kgs') {
            return round($weight * 1000, 2);
        } elseif ($unit === 'lbs') {
            return round($weight * 453.59237, 2);
        }

        return $weight;
    }

    /**
     * Gets the line item's image. Falls back to the parent item (If available) if the direct
     * item does not have an image
     *
     * @param CartItemInterface $item
     * @return string
     */
    private function getLineItemImage(CartItemInterface $item): string
    {
        $product = $this->productRepository->getById($item->getProductId());
        if ($product->getThumbnail() && $product->getThumbnail() !== 'no_selection') {
            return $this->productUrlBuilder->getUrl($product->getThumbnail(), 'product_thumbnail_image');
        }
        // Attempting to get the parent product if there is one
        if ($item->getParentItem()) {
            $image = $this->productRepository->getById($item->getParentItem()->getProductId())->getThumbnail();
            if ($image) {
                return $this->productUrlBuilder->getUrl($image, 'product_thumbnail_image');
            }
        }
        return $this->productUrlBuilder->getUrl('no_selection', 'product_thumbnail_image');
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
     * Add cart item configuration options to line item.
     *
     * @param CartItemInterface $item
     * @param array $lineItem
     * @return array
     */
    private function addConfigurableOptions(CartItemInterface $item, array $lineItem): array
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
    private function addCustomOptions(array $customOption, array $lineItem): array
    {
        $label = $this->escaper->escapeHtml($customOption['label']);
        $value = $this->configuration->getFormattedOptionValue(
            $customOption,
            [
                ['max_length' => 55],
            ]
        );
        $lineItem['line_item_properties'][\html_entity_decode($label)] = \html_entity_decode($value['value']) ?? '';
        return $lineItem;
    }

    /**
     * Takes in a bundle product line item and adds the items in the bundle to the line
     * item as line item properties
     *
     * @param CartItemInterface $item
     * @param array $lineItem
     * @return array
     */
    private function addBundleOptions(CartItemInterface $item, array $lineItem): array
    {
        $options = $this->bundleType->getOptionsCollection($item->getProduct());
        $children = $item->getChildren();
        $lineItem['line_item_properties'] = [];
        foreach (array_values($options->getItems()) as $i => $option) {
            $childItem = $children[$i] ?? null;
            if (!$childItem) {
                continue;
            }
            $qty = (int)$childItem->getQty();
            $name = $childItem->getName();
            $lineItem['line_item_properties'][$option['title']] = "$qty x $name";
        }
        return $lineItem;
    }

    /**
     * Get requires shipping considering product type
     *
     * @param CartItemInterface $item
     * @return bool
     */
    private function getRequiresShipping(CartItemInterface $item): bool
    {
        $type = $item->getProductType();
        return $type !== Virtual::TYPE_VIRTUAL && $type !== Downloadable::TYPE_DOWNLOADABLE;
    }

    /**
     * Converts a dollar amount to cents
     *
     * @param string|float $dollars
     * @return integer
     */
    private function convertToCents($dollars): int {
        return (int) round(floatval($dollars) * 100);
    }
}

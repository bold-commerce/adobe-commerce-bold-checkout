<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\SetQuoteAddresses;

use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterface;
use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultExtensionInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Set cart shipping addressees response.
 */
class Result implements ResultInterface
{
    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param CartInterface $quote
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(CartInterface $quote, ResultExtensionInterface $extensionAttributes = null)
    {
        $this->quote = $quote;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(): CartInterface
    {
        return $this->quote;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}

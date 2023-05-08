<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface;
use Bold\Checkout\Api\Data\Quote\ResultExtensionInterface;
use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Set cart shipping addressees response.
 */
class Result implements ResultInterface
{
    /**
     * @var CartInterface|null
     */
    private $quote;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @var ShippingMethodInterface[]
     */
    private $shippingMethods;

    /**
     * @var ErrorInterface[]
     */
    private $errors;

    /**
     * @var TotalsInterface|null
     */
    private $totals;

    /**
     * @param CartInterface|null $quote
     * @param TotalsInterface|null $totals
     * @param ErrorInterface[] $errors
     * @param ShippingMethodInterface[] $shippingMethods
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        ?CartInterface $quote = null,
        ?TotalsInterface $totals = null,
        array $errors = [],
        array $shippingMethods = [],
        ResultExtensionInterface $extensionAttributes = null
    ) {
        $this->quote = $quote;
        $this->extensionAttributes = $extensionAttributes;
        $this->errors = $errors;
        $this->shippingMethods = $shippingMethods;
        $this->totals = $totals;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(): ?CartInterface
    {
        return $this->quote;
    }

    /**
     * @inheritDoc
     */
    public function getTotals(): ?TotalsInterface
    {
        return $this->totals;
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethods(): array
    {
        return $this->shippingMethods;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}

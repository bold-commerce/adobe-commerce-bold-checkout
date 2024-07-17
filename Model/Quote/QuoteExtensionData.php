<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData as QuoteExtensionDataResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Bold quote data entity.
 */
class QuoteExtensionData extends AbstractModel
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(QuoteExtensionDataResource::class);
    }

    /**
     * Set quote entity id.
     *
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void
    {
        $this->setData(QuoteExtensionDataResource::QUOTE_ID, $quoteId);
    }

    /**
     * Retrieve quote id.
     *
     * @return int|null
     */
    public function getQuoteId(): ?int
    {
        return $this->getData(QuoteExtensionDataResource::QUOTE_ID)
            ? (int)$this->getData(QuoteExtensionDataResource::QUOTE_ID)
            : null;
    }

    /**
     * Set order should be created on Magento side.
     *
     * @param bool $orderCreated
     * @return void
     */
    public function setOrderCreated(bool $orderCreated): void
    {
        $this->setData(QuoteExtensionDataResource::ORDER_CREATED, $orderCreated);
    }

    /**
     * Get order should be created on Magento side.
     *
     * @return bool|null
     */
    public function getOrderCreated(): ?bool
    {
        return (bool)$this->getData(QuoteExtensionDataResource::ORDER_CREATED);
    }

    /**
     * Get API type.
     *
     * @return string|null
     */
    public function getApiType(): ?string
    {
        return $this->getData(QuoteExtensionDataResource::API_TYPE);
    }
}

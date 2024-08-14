<?php
declare(strict_types = 1);

//phpcs:disable Magento2.Annotation.MethodArguments.NoCommentBlock
//phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
//phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\BoldQuoteRepositoryInterface;
use Bold\Checkout\Api\Data\BoldQuoteInterface;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData as QuoteExtensionDataResource;
use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class BoldQuoteRepository implements BoldQuoteRepositoryInterface
{
    /**
     * @var QuoteExtensionDataResource
     */
    private $boldQuoteResource;

    /**
     * @var QuoteExtensionDataFactory
     */
    private $quoteExtensionDataFactory;

    public function __construct(
        QuoteExtensionDataResource $boldQuoteResource,
        QuoteExtensionDataFactory $quoteExtensionDataFactory,
    ) {
        $this->boldQuoteResource = $boldQuoteResource;
        $this->quoteExtensionDataFactory = $quoteExtensionDataFactory;
    }

    /**
     * @inheritDoc
     */
    public function getByCartId(int $cartId): BoldQuoteInterface
    {
        $boldQuote = $this->quoteExtensionDataFactory->create();
        $this->boldQuoteResource->load($boldQuote, $cartId, QuoteExtensionDataResource::QUOTE_ID);
        if ($boldQuote->getId() === null) {
            throw new NoSuchEntityException(__('No Bold Quote found for ID: %1.', $cartId));
        }

        return $boldQuote;
    }
}

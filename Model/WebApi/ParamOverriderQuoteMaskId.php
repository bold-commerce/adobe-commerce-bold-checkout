<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\WebApi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

class ParamOverriderQuoteMaskId implements ParamOverriderInterface
{
    private UserContextInterface $userContext;
    private CartRepositoryInterface $cartRepository;
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;

    public function __construct(
        UserContextInterface $userContext,
        CartRepositoryInterface $cartRepository,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->userContext = $userContext;
        $this->cartRepository = $cartRepository;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * @inheritDoc
     */
    public function getOverriddenValue(): ?string
    {
        $customerId = $this->userContext->getUserId();

        if ($this->userContext->getUserType() !== UserContextInterface::USER_TYPE_CUSTOMER || $customerId === null) {
            return null;
        }

        try {
            $quote = $this->cartRepository->getForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if (!$quote->getIsActive()) {
            return null;
        }

        try {
            $quoteMaskId = $this->quoteIdToMaskedQuoteId->execute($quote->getId());
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return $quoteMaskId;
    }
}

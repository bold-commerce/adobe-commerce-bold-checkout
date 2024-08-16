<?php
declare(strict_types = 1);

//phpcs:disable Magento2.Annotation.MethodArguments.NoCommentBlock
//phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
//phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace Bold\Checkout\Api;

use Bold\Checkout\Api\Data\BoldQuoteInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface BoldQuoteRepositoryInterface
{
    /**
     * @param int $cartId
     * @return BoldQuoteInterface
     * @throws NoSuchEntityException
     */
    public function getByCartId(int $cartId): BoldQuoteInterface;
}

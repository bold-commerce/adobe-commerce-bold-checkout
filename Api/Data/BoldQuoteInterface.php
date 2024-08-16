<?php
declare(strict_types = 1);

//phpcs:disable Magento2.Annotation.MethodArguments.NoCommentBlock
//phpcs:disable Magento2.Annotation.MethodArguments.ParamMissing
//phpcs:disable Magento2.Annotation.MethodAnnotationStructure.MethodAnnotation

namespace Bold\Checkout\Api\Data;

interface BoldQuoteInterface
{
    public function getId();

    public function getQuoteId(): ?int;

    public function setQuoteId(int $quoteId): void;

    public function getOrderCreated(): ?bool;

    public function setOrderCreated(bool $orderCreated): void;

    public function getApiType(): ?string;

    public function setApiType(string $apiType): void;

    public function getPublicOrderId(): ?string;

    public function setPublicOrderId(string $publicOrderId): void;
}

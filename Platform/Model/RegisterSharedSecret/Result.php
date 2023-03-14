<?php
declare(strict_types=1);

namespace Bold\Platform\Model\RegisterSharedSecret;

use Bold\Checkout\Api\Data\Response\ErrorInterface;
use Bold\Platform\Api\Data\RegisterSharedSecret\ResultExtensionInterface;
use Bold\Platform\Api\Data\RegisterSharedSecret\ResultInterface;

/**
 * Add shared secret result data model.
 */
class Result implements ResultInterface
{
    /**
     * @var string|null
     */
    private $shopId;

    /**
     * @var string|null
     */
    private $websiteCode;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @var ErrorInterface[]
     */
    private $errors;

    /**
     * @var int|null
     */
    private $websiteId;

    /**
     * @param string|null $shopId
     * @param string|null $websiteCode
     * @param int|null $websiteId
     * @param ErrorInterface[] $errors
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $shopId = null,
        string $websiteCode = null,
        int $websiteId = null,
        array $errors = [],
        ResultExtensionInterface $extensionAttributes = null
    ) {
        $this->shopId = $shopId;
        $this->websiteCode = $websiteCode;
        $this->extensionAttributes = $extensionAttributes;
        $this->errors = $errors;
        $this->websiteId = $websiteId;
    }

    /**
     * @inheritDoc
     */
    public function getShopId(): ?string
    {
        return $this->shopId;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteCode(): ?string
    {
        return $this->websiteCode;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId(): ?int
    {
        return $this->websiteId;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}

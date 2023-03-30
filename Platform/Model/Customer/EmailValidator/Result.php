<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Customer\EmailValidator;

use Bold\Platform\Api\Data\CustomerEmailValidator\ResultInterface;
use Bold\Platform\Api\Data\CustomerEmailValidator\ResultExtensionInterface;
use Bold\Platform\Api\Data\Response\ErrorInterface;

/**
 * Customer email validation result.
 */
class Result implements ResultInterface
{
    /**
     * @var ErrorInterface[]
     */
    private $errors;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param ErrorInterface[] $errors
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(array $errors = [], ResultExtensionInterface $extensionAttributes = null)
    {
        $this->errors = $errors;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function isValid(): bool
    {
        return !$this->errors;
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

<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Customer\EmailValidator;

use Bold\Platform\Api\Data\CustomerEmailValidator\ResultInterface;
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
     * @param ErrorInterface[] $errors
     */
    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
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
}

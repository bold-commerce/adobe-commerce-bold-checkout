<?php
declare(strict_types=1);

namespace Bold\Platform\Api;

use Bold\Platform\Api\Data\CustomerEmailValidator\ResultInterface;

/**
 * Validate customer email.
 */
interface CustomerEmailValidatorInterface
{
    /**
     * Validate given email.
     *
     * @param string $shopId
     * @param string $email
     * @return \Bold\Platform\Api\Data\CustomerEmailValidator\ResultInterface
     */
    public function validate(string $shopId, string $email): ResultInterface;
}

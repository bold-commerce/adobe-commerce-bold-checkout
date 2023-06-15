<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Quote\SendGuestCustomerInfoInterface;

class SendGuestCustomerInfo implements SendGuestCustomerInfoInterface
{

    /**
     * @inheritDoc
     */
    public function send(string $email, string $firstName, string $lastName): ResultInterface
    {
        // TODO: Implement send() method.
    }
}

<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;

interface SendGuestCustomerInfoInterface
{
    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return \Bold\Checkout\Api\Data\Http\Client\ResultInterface
     */
    public function send(string $email, string $firstName, string $lastName): ResultInterface;
}

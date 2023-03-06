<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder\Response;

/**
 * Place order response error data interface.
 */
interface ErrorInterface
{
    /**
     * Retrieve error code.
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Retrieve error type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retrieve error message.
     *
     * @return string
     */
    public function getMessage(): string;
}

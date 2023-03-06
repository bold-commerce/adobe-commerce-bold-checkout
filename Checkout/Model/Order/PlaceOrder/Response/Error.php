<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder\Response;

use Bold\Checkout\Api\Data\PlaceOrder\Response\ErrorInterface;

/**
 * Place order endpoint error data model.
 */
class Error implements ErrorInterface
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @param int $code
     * @param string $type
     * @param string $message
     */
    public function __construct(int $code, string $type, string $message)
    {
        $this->code = $code;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}

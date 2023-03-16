<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Response;

use Bold\Platform\Api\Data\Response\ErrorExtensionInterface;
use Bold\Platform\Api\Data\Response\ErrorInterface;

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
     * @var ErrorExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $message
     * @param string $type
     * @param int $code
     * @param ErrorExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $message,
        string $type = 'server.internal_error',
        int $code = 500,
        ErrorExtensionInterface $extensionAttributes = null
    ) {
        $this->message = $message;
        $this->type = $type;
        $this->code = $code;
        $this->extensionAttributes = $extensionAttributes;
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

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ErrorExtensionInterface
    {
        return $this->extensionAttributes;
    }
}

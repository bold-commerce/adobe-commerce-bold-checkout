<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Http\ResponseInterface;

/**
 * Http client response data model.
 */
class Response implements ResponseInterface
{
    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $body;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param int $status
     * @param array $body
     * @param array $errors
     */
    public function __construct(int $status, array $body = [], array $errors = [])
    {
        $this->status = $status;
        $this->body = $body;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return $this->status;
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
    public function getBody(): array
    {
        return $this->body;
    }
}

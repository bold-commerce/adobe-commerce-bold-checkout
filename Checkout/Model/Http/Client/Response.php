<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client;

use Bold\Checkout\Api\Data\Http\Client\ResponseExtensionInterface;
use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;
use Exception;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Http client response data model.
 */
class Response implements ResponseInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ResponseExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param Json $json
     * @param ClientInterface $client
     * @param ResponseExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        Json $json,
        ClientInterface $client,
        ResponseExtensionInterface $extensionAttributes = null
    ) {
        $this->client = $client;
        $this->json = $json;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return $this->client->getStatus();
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        try {
            $body = $this->json->unserialize($this->client->getBody());
        } catch (Exception $e) {
            $body = [];
        }
        return $this->getErrorsFromBody($body);
    }

    /**
     * @inheritDoc
     */
    public function getBody(): array
    {
        try {
            $body = $this->json->unserialize($this->client->getBody());
        } catch (Exception $e) {
            $body = [];
        }
        return $this->getErrorsFromBody($body) ? [] : $body;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResponseExtensionInterface
    {
        return $this->extensionAttributes;
    }

    /**
     * Retrieve errors from response body.
     *
     * @param array $body
     * @return array
     */
    private function getErrorsFromBody(array $body): array
    {
        $errors = $body['errors'] ?? [];
        if (isset($body['error'])) {
            $errors = [
                $body['error_description'] ?? $body['error'],
            ];
        }
        return $errors;
    }
}

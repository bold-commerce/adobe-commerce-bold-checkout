<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\PaymentStyleManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Payment iframe styles management.
 */
class PaymentStyleManagement implements PaymentStyleManagementInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param ClientInterface $client
     * @param Json $serializer
     */
    public function __construct(
        ClientInterface $client,
        Json            $serializer
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @ingeritDoc
     */
    public function update(int $websiteId, array $data): void
    {
        $result = $this->client->post($websiteId, self::PAYMENT_CSS_API_URI, $data);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            if (is_array($error)) {
                $error = $this->serializer->serialize($error);
            }
            throw new \Exception($error);
        }
    }

    /**
     * @ingeritDoc
     */
    public function delete(int $websiteId): void
    {
        $result = $this->client->delete($websiteId, self::PAYMENT_CSS_API_URI, []);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            if (is_array($error)) {
                $error = $this->serializer->serialize($error);
            }
            throw new \Exception($error);
        }
    }

    public function get(int $websiteId): array
    {
        $result = $this->client->get($websiteId, self::PAYMENT_CSS_API_URI);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            if (is_array($error)) {
                $error = $this->serializer->serialize($error);
            }
            throw new \Exception($error);
        }

        return $result->getBody()['data']['style_sheet'];
    }
}

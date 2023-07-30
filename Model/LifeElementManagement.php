<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\LifeElementManagementInterface;

/**
 * (LiFE) Elements management.
 */
class LifeElementManagement implements LifeElementManagementInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(
        ClientInterface $client
    ) {
        $this->client = $client;
    }

    /**
     * @ingeritDoc
     */
    public function create(int $websiteId, array $elementData): string
    {
        $result = $this->client->post($websiteId, self::LIFE_ELEMENTS_API_URI, $elementData);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            throw new \Exception($error);
        }
        $lifeElements = $result->getBody()['data']['life_elements'];

        return current($lifeElements)['public_id'];
    }

    /**
     * @ingeritDoc
     */
    public function getList(int $websiteId): array
    {
        $result = $this->client->get($websiteId, self::LIFE_ELEMENTS_API_URI);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            throw new \Exception($error);
        }

        return $result->getBody()['data']['life_elements'];
    }

    /**
     * @ingeritDoc
     */
    public function update(int $websiteId, string $publicElementId, array $elementData): string
    {
        $result = $this->client->patch($websiteId, self::LIFE_ELEMENTS_API_URI . '/' . $publicElementId, $elementData);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            throw new \Exception($error);
        }
        $lifeElements = $result->getBody()['data']['life_elements'];

        return current($lifeElements)['public_id'];
    }

    /**
     * @ingeritDoc
     */
    public function delete(int $websiteId, string $publicElementId): void
    {
        $result = $this->client->delete($websiteId, self::LIFE_ELEMENTS_API_URI . '/' . $publicElementId, []);
        if ($result->getErrors()) {
            $error = current($result->getErrors());
            throw new \Exception($error);
        }
    }
}

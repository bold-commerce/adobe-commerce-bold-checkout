<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Platform\Model\Synchronizer\EntitiesSynchronizerInterface;
use Bold\Platform\Model\Synchronizer\ProductSynchronizer\GetPreparedProducts;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Synchronize entities by provided ids.
 */
class EntitiesSynchronizer implements EntitiesSynchronizerInterface
{
    private const CHUNK_SIZE = 100;

    /**
     * @var \Bold\Checkout\Api\Http\ClientInterface
     */
    private $client;

    /**
     * @var \Bold\Platform\Model\Synchronizer\GetPreparedEntities
     */
    private $getPreparedEntities;

    /**
     * @var string
     */
    private $synchronizationMethod;

    /**
     * @var string
     */
    private $synchronizationUrl;

    /**
     * @var int
     */
    private $chunkSize;

    /**
     * @param \Bold\Checkout\Api\Http\ClientInterface $client
     * @param \Bold\Platform\Model\Synchronizer\GetPreparedEntities $getPreparedEntities
     * @param string $synchronizationMethod
     * @param string $synchronizationUrl
     * @param int $chunkSize
     */
    public function __construct(
        ClientInterface     $client,
        GetPreparedEntities $getPreparedEntities,
        string              $synchronizationMethod,
        string              $synchronizationUrl,
        int                 $chunkSize = self::CHUNK_SIZE
    ) {
        $this->client = $client;
        $this->getPreparedEntities = $getPreparedEntities;
        $this->synchronizationMethod = $synchronizationMethod;
        $this->synchronizationUrl = $synchronizationUrl;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @inheritDoc
     */
    public function synchronize(array $ids): void
    {
        $entityIdsChunks = array_chunk($ids, $this->chunkSize);
        foreach ($entityIdsChunks as $entityIds) {
            if (!$entityIds) {
                break;
            }
            $entities = $this->getPreparedEntities->getItems($entityIds);
            $entitiesData = array_map(
                function (AbstractExtensibleModel $entity) {
                    return $entity->getData();
                },
                $entities
            );
            $this->client->call(
                $this->synchronizationMethod,
                $this->synchronizationUrl,
                $entitiesData
            );
        }
    }
}

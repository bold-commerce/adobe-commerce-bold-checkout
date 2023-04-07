<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Queue\Handler\Category;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Model\Queue\RequestInterface;
use Bold\Platform\Model\Sync\GetCategories;
use Exception;

/**
 * Bold Category synchronization queue handler.
 */
class Synchronize
{
    private const CHUNK_SIZE = 500;
    private const URL = '/{{shopId}}/webhooks/categories/saved';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetCategories
     */
    private $getCategories;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ConfigInterface $config
     * @param GetCategories $getCategories
     * @param ClientInterface $client
     */
    public function __construct(
        ConfigInterface $config,
        GetCategories   $getCategories,
        ClientInterface $client
    ) {
        $this->config = $config;
        $this->getCategories = $getCategories;
        $this->client = $client;
    }

    /**
     * Handle Bold Category synchronization queue message.
     *
     * @param RequestInterface $request
     * @return void
     * @throws Exception
     */
    public function handle(RequestInterface $request): void
    {
        if (!$this->config->isCheckoutEnabled($request->getWebsiteId())) {
            return;
        }
        $idsChunks = array_chunk($request->getEntityIds(), self::CHUNK_SIZE);
        foreach ($idsChunks as $entityIds) {
            $items = $this->getCategories->getItems($request->getWebsiteId(), $entityIds);
            $this->client->post($request->getWebsiteId(), self::URL, $items);
        }
    }
}

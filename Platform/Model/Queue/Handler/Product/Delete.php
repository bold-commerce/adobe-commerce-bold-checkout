<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Queue\Handler\Product;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Model\Queue\RequestInterface;
use Bold\Platform\Model\Sync\GetProducts;
use Exception;

/**
 * Bold Products deletion queue handler.
 */
class Delete
{
    private const METHOD = 'POST';
    private const URL = '/';
    private const CHUNK_SIZE = 500;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetProducts
     */
    private $getProducts;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ConfigInterface $config
     * @param GetProducts $getProducts
     * @param ClientInterface $client
     */
    public function __construct(
        ConfigInterface $config,
        GetProducts     $getProducts,
        ClientInterface $client
    ) {
        $this->config = $config;
        $this->getProducts = $getProducts;
        $this->client = $client;
    }

    /**
     * Handle Bold Products synchronization queue message.
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
            $items = $this->getProducts->getItems($request->getWebsiteId(), $entityIds);
            $this->client->call($request->getWebsiteId(), self::METHOD, self::URL, $items);
        }
    }
}

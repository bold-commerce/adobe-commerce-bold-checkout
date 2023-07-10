<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Queue\Handler\Customer;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Queue\RequestInterface;
use Bold\Checkout\Model\Sync\GetCustomers;
use Exception;

/**
 * Bold Customer deletion queue handler.
 */
class Delete
{
    private const CHUNK_SIZE = 500;
    private const URL = '/{{shopId}}/webhooks/customers/deleted';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetCustomers
     */
    private $getCustomers;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ConfigInterface $config
     * @param GetCustomers $getCustomers
     * @param ClientInterface $client
     */
    public function __construct(
        ConfigInterface $config,
        GetCustomers $getCustomers,
        ClientInterface $client
    ) {
        $this->config = $config;
        $this->getCustomers = $getCustomers;
        $this->client = $client;
    }

    /**
     * Handle Bold Customer synchronization queue message.
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
        foreach ($idsChunks as $idsChunk) {
            $this->client->post($request->getWebsiteId(), self::URL, ['ids' => $idsChunk]);
        }
    }
}

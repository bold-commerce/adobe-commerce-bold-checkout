<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client\Command;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Data\Http\Client\ResultInterfaceFactory;
use Bold\Checkout\Model\Http\Client\RequestsLogger;
use Magento\Framework\HTTP\ClientInterface;

/**
 * Perform and log get request command.
 */
class GetCommand
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestsLogger
     */
    private $logger;

    /**
     * @var ResultInterfaceFactory
     */
    private $responseFactory;

    /**
     * @param ResultInterfaceFactory $responseFactory
     * @param ClientInterface $client
     * @param RequestsLogger $logger
     */
    public function __construct(
        ResultInterfaceFactory $responseFactory,
        ClientInterface $client,
        RequestsLogger $logger
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Perform and log get request.
     *
     * @param int $websiteId
     * @param string $url
     * @param array $headers
     * @return ResultInterface
     */
    public function execute(int $websiteId, string $url, array $headers): ResultInterface
    {
        $this->logger->logRequest($websiteId, $url, 'GET');
        $this->client->setHeaders($headers);
        $this->client->get($url);
        $this->logger->logResponse($websiteId, $this->client);
        return $this->responseFactory->create(
            [
                'client' => $this->client,
            ]
        );
    }
}

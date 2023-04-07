<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Bold http client requests|responses logger.
 */
class RequestsLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var string
     */
    private $logId = 'n/a';

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param Random $random
     * @param Json $json
     */
    public function __construct(LoggerInterface $logger, ConfigInterface $config, Random $random, Json $json)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->random = $random;
        $this->json = $json;
    }

    /**
     * Log outgoing request.
     *
     * @param int $websiteId
     * @param string $url
     * @param string $method
     * @param array|null $data
     * @return void
     */
    public function logRequest(int $websiteId, string $url, string $method, ?array $data = null): void
    {
        if (!$this->config->getLogIsEnabled($websiteId)) {
            return;
        }
        try {
            $this->logId = $this->random->getRandomString(10);
        } catch (LocalizedException $e) {
            $this->logId = 'n/a';
        }
        $this->logger->debug($this->logId . ' - Outgoing Call: ' . $method . ' ' . $url);
        $data && $this->logger->debug($this->logId . ' - Outgoing Call Data: ' . $this->json->serialize($data));
    }

    /**
     * Log response from client.
     *
     * @param int $websiteId
     * @param ClientInterface $client
     * @return void
     */
    public function logResponse(int $websiteId, ClientInterface $client): void
    {
        if (!$this->config->getLogIsEnabled($websiteId)) {
            return;
        }
        $this->logger->debug($this->logId . ' - Outgoing Call Code: ' . $client->getStatus());
        $this->logger->debug($this->logId . ' - Outgoing Call Result: ' . $client->getBody());
    }
}

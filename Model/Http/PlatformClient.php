<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\SystemInfoHeaders;
use DateTime;
use Magento\Framework\Exception\LocalizedException;

/**
 * M2 Platform Connector Http Client.
 */
class PlatformClient implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var GetCommand
     */
    private $getCommand;

    /**
     * @var PostCommand
     */
    private $postCommand;

    /**
     * @var SystemInfoHeaders
     */
    private $systemInfoHeaders;

    /**
     * @param ConfigInterface $config
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     * @param SystemInfoHeaders $systemInfoHeaders
     */
    public function __construct(
        ConfigInterface $config,
        GetCommand $getCommand,
        PostCommand $postCommand,
        SystemInfoHeaders $systemInfoHeaders
    ) {
        $this->config = $config;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
        $this->systemInfoHeaders = $systemInfoHeaders;
    }

    /**
     * @inheritDoc
     */
    public function get(int $websiteId, string $url): ResultInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->getCommand->execute($websiteId, $url, $headers);
    }

    /**
     * @inheritDoc
     */
    public function post(int $websiteId, string $url, array $data): ResultInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->postCommand->execute($websiteId, $url, $headers, $data);
    }

    /**
     * @inheritDoc
     */
    public function put(int $websiteId, string $url, array $data): ResultInterface
    {
        throw new LocalizedException(__('Put method is not implemented.'));
    }

    /**
     * @inheritDoc
     */
    public function patch(int $websiteId, string $url, array $data): ResultInterface
    {
        throw new LocalizedException(__('Patch method is not implemented.'));
    }

    /**
     * @inheritDoc
     */
    public function delete(int $websiteId, string $url, array $data): ResultInterface
    {
        throw new LocalizedException(__('Delete method is not implemented.'));
    }

    /**
     * Build platform url.
     *
     * @param int $websiteId
     * @param string $url
     * @return string
     */
    private function getUrl(int $websiteId, string $url): string
    {
        $shopId = $this->config->getShopId($websiteId);
        return $this->config->getPlatformConnectorUrl($websiteId) . str_replace('{{shopId}}', $shopId, $url);
    }

    /**
     * Build platform headers.
     *
     * @param int $websiteId
     * @return array
     */
    private function getHeaders(int $websiteId): array
    {
        $secret = $this->config->getSharedSecret($websiteId);
        $timestamp = date(DateTime::RFC3339);
        $hmac = base64_encode(hash_hmac('sha256', $timestamp, $secret, true));
        $systemInfoHeaders = $this->config->isSystemInfoEnabled($websiteId) ? $this->systemInfoHeaders->getData() : [];

        return array_merge(
            [
                'X-HMAC-Timestamp' => $timestamp,
                'X-HMAC' => $hmac,
                'Content-Type' => 'application/json',
            ],
            $systemInfoHeaders
        );
    }
}

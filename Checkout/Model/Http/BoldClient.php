<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResponseInterface;
use Bold\Checkout\Api\Data\Http\Client\ResponseInterfaceFactory;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Magento\Framework\Exception\LocalizedException;

/**
 * Client to perform http request to Bold.
 */
class BoldClient implements ClientInterface
{
    private const URL = 'https://api.boldcommerce.com/';
    private const BOLD_API_VERSION_DATE = '2022-10-14';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UserAgent
     */
    private $userAgent;

    /**
     * @var GetCommand
     */
    private $getCommand;

    /**
     * @var PostCommand
     */
    private $postCommand;

    /**
     * @param ConfigInterface $config
     * @param UserAgent $userAgent
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     */
    public function __construct(
        ConfigInterface $config,
        UserAgent $userAgent,
        GetCommand $getCommand,
        PostCommand $postCommand
    ) {
        $this->config = $config;
        $this->userAgent = $userAgent;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
    }

    /**
     * @inheritDoc
     */
    public function get(int $websiteId, string $url): ResponseInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->getCommand->execute($websiteId, $url, $headers);
    }

    /**
     * @inheritDoc
     */
    public function post(int $websiteId, string $url, array $data): ResponseInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->postCommand->execute($websiteId, $url, $headers, $data);
    }

    /**
     * @inheritDoc
     */
    public function put(int $websiteId, string $url, array $data): ResponseInterface
    {
        throw new LocalizedException(__('Put method is not implemented.'));
    }

    /**
     * @inheritDoc
     */
    public function patch(int $websiteId, string $url, array $data): ResponseInterface
    {
        throw new LocalizedException(__('Patch method is not implemented.'));
    }

    /**
     * @inheritDoc
     */
    public function delete(int $websiteId, string $url): ResponseInterface
    {
        throw new LocalizedException(__('Delete method is not implemented.'));
    }

    /**
     * Get request headers.
     *
     * @param int $websiteId
     * @return array
     */
    private function getHeaders(int $websiteId): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config->getApiToken($websiteId),
            'Content-Type' => 'application/json',
            'User-Agent' => $this->userAgent->getUserAgentData(),
            'Bold-API-Version-Date' => self::BOLD_API_VERSION_DATE,
        ];
    }

    /**
     * Get request url.
     *
     * @param int $websiteId
     * @param string $url
     * @return string
     */
    private function getUrl(int $websiteId, string $url): string
    {
        if (!$this->config->getShopId($websiteId)) {
            return self::URL . $url;
        }
        return self::URL . str_replace('{{shopId}}', $this->config->getShopId($websiteId), $url);
    }
}

<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Data\Http\Client\ResultInterfaceFactory;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PatchCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Magento\Framework\Exception\LocalizedException;

/**
 * Client to perform http request to Bold.
 */
class BoldClient implements ClientInterface
{
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
     * @var PatchCommand
     */
    private $patchCommand;

    /**
     * @param ConfigInterface $config
     * @param UserAgent $userAgent
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     * @param PatchCommand $patchCommand
     */
    public function __construct(
        ConfigInterface $config,
        UserAgent $userAgent,
        GetCommand $getCommand,
        PostCommand $postCommand,
        PatchCommand $patchCommand
    ) {
        $this->config = $config;
        $this->userAgent = $userAgent;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
        $this->patchCommand = $patchCommand;
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
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->patchCommand->execute($websiteId, $url, $headers, $data);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $websiteId, string $url, array $data): ResultInterface
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
        $apiUrl = $this->config->getApiUrl($websiteId);
        if (!$this->config->getShopId($websiteId)) {
            return $apiUrl . $url;
        }
        return $apiUrl . str_replace('{{shopId}}', $this->config->getShopId($websiteId), $url);
    }
}

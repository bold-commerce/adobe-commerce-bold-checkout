<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\DeleteCommand;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PatchCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\Command\PutCommand;
use Bold\Checkout\Model\Http\Client\SystemInfoHeaders;
use Bold\Checkout\Model\Http\Client\UserAgent;

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
     * @var DeleteCommand
     */
    private $deleteCommand;

    /**
     * @var PutCommand
     */
    private $putCommand;

    /**
     * @var SystemInfoHeaders
     */
    private $systemInfoHeaders;

    /**
     * @param ConfigInterface $config
     * @param UserAgent $userAgent
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     * @param PatchCommand $patchCommand
     * @param DeleteCommand $deleteCommand
     * @param PutCommand $putCommand
     * @param SystemInfoHeaders $systemInfoHeaders
     */
    public function __construct(
        ConfigInterface $config,
        UserAgent $userAgent,
        GetCommand $getCommand,
        PostCommand $postCommand,
        PatchCommand $patchCommand,
        DeleteCommand $deleteCommand,
        PutCommand $putCommand,
        SystemInfoHeaders $systemInfoHeaders
    ) {
        $this->config = $config;
        $this->userAgent = $userAgent;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
        $this->patchCommand = $patchCommand;
        $this->deleteCommand = $deleteCommand;
        $this->putCommand = $putCommand;
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
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->putCommand->execute($websiteId, $url, $headers, $data);
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
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders($websiteId);
        return $this->deleteCommand->execute($websiteId, $url, $headers, $data);
    }

    /**
     * Get request headers.
     *
     * @param int $websiteId
     * @return array
     */
    private function getHeaders(int $websiteId): array
    {
        $systemInfoHeaders = $this->config->isSystemInfoEnabled($websiteId) ? $this->systemInfoHeaders->getData() : [];

        return array_merge(
            [
                'Authorization' => 'Bearer ' . $this->config->getApiToken($websiteId),
                'Content-Type' => 'application/json',
                'User-Agent' => $this->userAgent->getUserAgentData(),
                'Bold-API-Version-Date' => self::BOLD_API_VERSION_DATE,
            ],
            $systemInfoHeaders
        );
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

        if (strpos($apiUrl, 'bold.ninja') !== false) {
            $parseApiUrl = parse_url($apiUrl);
            $scheme = $parseApiUrl['scheme'];
            $host = $parseApiUrl['host'];
            $path = $parseApiUrl['path'];
            $tunnelDomain = ltrim($path, '/');
            $baseApiUrl = $scheme.'://'.$host.'/';

            if ($url === 'shops/v1/info') {
                $apiUrl = $baseApiUrl;
            }

            if (strpos($url, 'checkout_sidekick') !== false) {
                $apiUrl = $baseApiUrl.'sidekick-'.$tunnelDomain;
            }
        }

        if (!$this->config->getShopId($websiteId)) {
            return $apiUrl.$url;
        }

        return $apiUrl.str_replace('{{shopId}}', $this->config->getShopId($websiteId), $url);
    }
}

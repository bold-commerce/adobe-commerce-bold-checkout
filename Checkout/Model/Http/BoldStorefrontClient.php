<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\UserAgent;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

/**
 * Bold Storefront API client.
 */
class BoldStorefrontClient implements ClientInterface
{
    private const URL = 'https://api.boldcommerce.com/checkout/storefront/';

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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ConfigInterface $config
     * @param Session $checkoutSession
     * @param UserAgent $userAgent
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     */
    public function __construct(
        ConfigInterface $config,
        Session $checkoutSession,
        GetCommand $getCommand,
        PostCommand $postCommand
    ) {
        $this->config = $config;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function get(int $websiteId, string $url): ResultInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders();
        return $this->getCommand->execute($websiteId, $url, $headers);
    }

    /**
     * @inheritDoc
     */
    public function post(int $websiteId, string $url, array $data): ResultInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders();
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
    public function delete(int $websiteId, string $url): ResultInterface
    {
        throw new LocalizedException(__('Delete method is not implemented.'));
    }

    /**
     * Get request headers.
     *
     * @return array
     * @throws LocalizedException
     */
    private function getHeaders(): array
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            throw new LocalizedException(__('Bold Checkout data is not set.'));
        }
        return [
            'Authorization' => 'Bearer ' . $boldCheckoutData['data']['jwt_token'],
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Get request url.
     *
     * @param int $websiteId
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    private function getUrl(int $websiteId, string $path): string
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            throw new LocalizedException(__('Bold Checkout data is not set.'));
        }
        $publicOrderId = $boldCheckoutData['data']['public_order_id'];
        return self::URL . $this->config->getShopId($websiteId) . '/' . $publicOrderId . '/' . $path;
    }
}
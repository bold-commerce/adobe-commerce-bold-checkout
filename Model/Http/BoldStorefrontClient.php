<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Command\DeleteCommand;
use Bold\Checkout\Model\Http\Client\Command\GetCommand;
use Bold\Checkout\Model\Http\Client\Command\PostCommand;
use Bold\Checkout\Model\Http\Client\Command\PutCommand;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

/**
 * Bold Storefront API client.
 */
class BoldStorefrontClient implements ClientInterface
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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var DeleteCommand
     */
    private $deleteCommand;

    /**
     * @var PutCommand
     */
    private $putCommand;

    /**
     * @param ConfigInterface $config
     * @param Session $checkoutSession
     * @param GetCommand $getCommand
     * @param PostCommand $postCommand
     * @param DeleteCommand $deleteCommand
     * @param PutCommand $putCommand
     */
    public function __construct(
        ConfigInterface $config,
        Session $checkoutSession,
        GetCommand $getCommand,
        PostCommand $postCommand,
        DeleteCommand $deleteCommand,
        PutCommand $putCommand
    ) {
        $this->config = $config;
        $this->getCommand = $getCommand;
        $this->postCommand = $postCommand;
        $this->checkoutSession = $checkoutSession;
        $this->deleteCommand = $deleteCommand;
        $this->putCommand = $putCommand;
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
        $result = $this->postCommand->execute($websiteId, $url, $headers, $data);
        $applicationState = $result->getBody()['data']['application_state'] ?? null;
        if (!$result->getErrors() && $applicationState) {
            $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
            $boldCheckoutData['data']['application_state'] = $applicationState;
            $this->checkoutSession->setBoldCheckoutData($boldCheckoutData);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function put(int $websiteId, string $url, array $data): ResultInterface
    {
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders();
        $result = $this->putCommand->execute($websiteId, $url, $headers, $data);
        $applicationState = $result->getBody()['data']['application_state'] ?? null;
        if (!$result->getErrors() && $applicationState) {
            $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
            $boldCheckoutData['data']['application_state'] = $applicationState;
            $this->checkoutSession->setBoldCheckoutData($boldCheckoutData);
        }

        return $result;
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
        $url = $this->getUrl($websiteId, $url);
        $headers = $this->getHeaders();
        $result = $this->deleteCommand->execute($websiteId, $url, $headers, $data);
        $applicationState = $result->getBody()['data']['application_state'] ?? null;
        if (!$result->getErrors() && $applicationState) {
            $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
            $boldCheckoutData['data']['application_state'] = $applicationState;
            $this->checkoutSession->setBoldCheckoutData($boldCheckoutData);
        }

        return $result;
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
    public function getUrl(int $websiteId, string $path): string
    {
        $apiUrl = $this->config->getApiUrl($websiteId) . 'checkout/storefront/';
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            throw new LocalizedException(__('Bold Checkout data is not set.'));
        }
        $publicOrderId = $boldCheckoutData['data']['public_order_id'];
        return $apiUrl . $this->config->getShopId($websiteId) . '/' . $publicOrderId . '/' . $path;
    }
}

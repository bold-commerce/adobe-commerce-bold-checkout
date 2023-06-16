<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Quote\SendBillingAddressInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Send billing address to Bold.
 */
class SendBillingAddress implements SendBillingAddressInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var GetWebsiteIdByShopId
     */
    private $getWebsiteIdByShopId;

    /**
     * @var Converter
     */
    private $addressConverter;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ClientInterface $client
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param Converter $addressConverter
     * @param Session $checkoutSession
     */
    public function __construct(
        ClientInterface $client,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        Converter $addressConverter,
        Session $checkoutSession
    ) {
        $this->client = $client;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->addressConverter = $addressConverter;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function send(string $shopId, AddressInterface $address): void
    {
        $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
        if (!$websiteId) {
            throw new LocalizedException(__('Website ID not found for shop ID: %1', $shopId));
        }
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            throw new LocalizedException(__('Bold Checkout data not found'));
        }
        $result = null;
        $addressPayload = $this->addressConverter->convert($address);
        $billingAddress = $boldCheckoutData['data']['application_state']['addresses']['billing'] ?? [];
        if (!$this->isAddressSynced($billingAddress, $addressPayload)) {
            $result = $this->client->post($websiteId, 'addresses/billing', $addressPayload);
        }
        if ($result && $result->getErrors()) {
            throw new LocalizedException(__('Could not send billing address'));
        }
    }

    /**
     * Verify if the billing address is the same as the one sent to Bold.
     *
     * @param array $existingAddress
     * @param array $addressPayload
     * @return bool
     */
    public function isAddressSynced(array $existingAddress, array $addressPayload): bool
    {
        $addressPayload['id'] = $existingAddress['id'] ?? null;
        ksort($existingAddress);
        ksort($addressPayload);
        return $existingAddress === $addressPayload;
    }
}

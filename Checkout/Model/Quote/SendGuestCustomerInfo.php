<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Quote\SendGuestCustomerInfoInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;

/**
 * Send guest customer information to Bold.
 */
class SendGuestCustomerInfo implements SendGuestCustomerInfoInterface
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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ClientInterface $client
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param Session $checkoutSession
     */
    public function __construct(
        ClientInterface $client,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        Session $checkoutSession
    ) {
        $this->client = $client;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritDoc
     */
    public function send(string $shopId, string $email, string $firstName, string $lastName): void
    {
        $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
        if (!$websiteId) {
            throw new LocalizedException(__('Website ID not found for shop ID: %1', $shopId));
        }
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        try {
            $customerPayload = [
                'email_address' => \trim($email),
                'first_name' => \trim($firstName),
                'last_name' => \trim($lastName),
            ];
            $result = null;
            $customer = $boldCheckoutData['data']['application_state']['customer'] ?? [];
            if (!$this->isCustomerSynced($customer, $customerPayload)) {
                $result = $this->client->post($websiteId, 'customer/guest', $customerPayload);
            }
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not send customer information: %1', $e->getMessage()));
        }
        if ($result && $result->getErrors()) {
            throw new LocalizedException(__('Could not send customer information'));
        }
    }

    /**
     * Check if the customer already send to Bold Side.
     *
     * @param array $customer
     * @param array $customerPayload
     * @return bool
     */
    private function isCustomerSynced(array $customer, array $customerPayload): bool
    {
        $emailAddress = $customerPayload['email_address'] ?? null;
        $firstName = $customerPayload['first_name'] ?? null;
        $lastName = $customerPayload['last_name'] ?? null;
        return $customer['email_address'] === $emailAddress
            && $customer['first_name'] === $firstName
            && $customer['last_name'] === $lastName;
    }
}

<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Quote\SendGuestCustomerInfoInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
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
     * @param ClientInterface $client
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param Converter $addressConverter
     */
    public function __construct(
        ClientInterface $client,
        GetWebsiteIdByShopId $getWebsiteIdByShopId
    ) {
        $this->client = $client;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
    }

    /**
     * @inheritDoc
     */
    public function send(string $shopId, string $email, string $firstName, string $lastName): ResultInterface
    {
        $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
        if (!$websiteId) {
            throw new LocalizedException(__('Website ID not found for shop ID: %1', $shopId));
        }
        try {
            $data = [
                'email_address' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];
            $result = $this->client->post($websiteId, 'customer/guest', $data);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not send customer information: %1', $e->getMessage()));
        }
        if ($result->getErrors()) {
            throw new LocalizedException(__('Could not send customer information'));
        }
        return $result;
    }
}

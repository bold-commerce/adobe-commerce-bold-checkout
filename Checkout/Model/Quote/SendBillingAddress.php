<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Quote\SendBillingAddressInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Bold\Checkout\Model\ResourceModel\GetWebsiteIdByShopId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;

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
     * @param ClientInterface $client
     * @param GetWebsiteIdByShopId $getWebsiteIdByShopId
     * @param Converter $addressConverter
     */
    public function __construct(
        ClientInterface $client,
        GetWebsiteIdByShopId $getWebsiteIdByShopId,
        Converter $addressConverter
    ) {
        $this->client = $client;
        $this->getWebsiteIdByShopId = $getWebsiteIdByShopId;
        $this->addressConverter = $addressConverter;
    }

    /**
     * @inheritDoc
     */
    public function send(string $shopId, AddressInterface $address): ResultInterface
    {
        $websiteId = $this->getWebsiteIdByShopId->getWebsiteId($shopId);
        if (!$websiteId) {
            throw new LocalizedException(__('Website ID not found for shop ID: %1', $shopId));
        }
        try {
            $data = $this->addressConverter->convert($address);
            return $this->client->post($websiteId, 'addresses/billing', $data);
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not send billing address: %1', $e->getMessage()));
        }
    }
}

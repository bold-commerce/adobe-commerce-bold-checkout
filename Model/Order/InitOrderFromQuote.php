<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\InitOrderFromQuote\OrderDataProcessorInterface;
use Bold\Checkout\Model\Quote\GetCartLineItems;
use Bold\Checkout\Model\Quote\QuoteAction;
use Bold\Checkout\Model\Quote\SetQuoteExtensionData;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Init order data from quote on Bold side.
 */
class InitOrderFromQuote
{
    private const INIT_URL = '/checkout/orders/{{shopId}}/init';
    private const FLOW_ID = 'Bold-Magento2';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var GetCartLineItems
     */
    private $getCartLineItems;

    /**
     * @var QuoteAction
     */
    private $quoteAction;

    /**
     * @var SetQuoteExtensionData
     */
    private $setQuoteExtensionData;

    /**
     * @var array
     */
    private $orderDataProcessors;

    /**
     * @param ClientInterface $client
     * @param CollectionFactory $countryCollectionFactory
     * @param GetCartLineItems $getCartLineItems
     * @param QuoteAction $quoteAction
     * @param SetQuoteExtensionData $setQuoteExtensionData
     * @param OrderDataProcessorInterface[] $orderDataProcessors
     */
    public function __construct(
        ClientInterface $client,
        CollectionFactory $countryCollectionFactory,
        GetCartLineItems $getCartLineItems,
        QuoteAction $quoteAction,
        SetQuoteExtensionData $setQuoteExtensionData,
        array $orderDataProcessors = []
    ) {
        $this->client = $client;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->getCartLineItems = $getCartLineItems;
        $this->quoteAction = $quoteAction;
        $this->setQuoteExtensionData = $setQuoteExtensionData;
        $this->orderDataProcessors = $orderDataProcessors;
    }

    /**
     * Initialize order on Bold side.
     *
     * @param CartInterface $quote
     * @param string $flowId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function init(CartInterface $quote, string $flowId = self::FLOW_ID): array
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $body = [
            'flow_id' => $flowId,
            'api_session_id' => (string)$quote->getId(),
            'cart_items' => $this->getCartLineItems->getItems($quote),
            'actions' => $this->quoteAction->getActionsData($quote),
            'order_meta_data' => [
                'cart_parameters' => [
                    'quote_id' => $quote->getId(),
                    'store_id' => $quote->getStoreId(),
                    'website_id' => $websiteId,
                ],
                'note_attributes' => [
                    'quote_id' => $quote->getId(),
                ],
            ],
        ];

        if ($quote->getCustomer()->getId()) {
            $countries = $this->getCustomerCountries($quote);
            $customerAddresses = [];
            foreach ($quote->getCustomer()->getAddresses() as $address) {
                $customerAddresses[] = $this->getAddress($address, $countries);
            }
            $body['customer'] = [
                'first_name' => (string)$quote->getCustomerFirstname(),
                'last_name' => (string)$quote->getCustomerLastname(),
                'email_address' => (string)$quote->getCustomerEmail(),
                'platform_id' => (string)$quote->getCustomerId(),
                'accepts_marketing' => false,
                'saved_addresses' => $customerAddresses,
            ];
        }
        $orderData = $this->client->post($websiteId, self::INIT_URL, $body)->getBody();
        $publicOrderId = $orderData['data']['public_order_id'] ?? null;
        if (!$publicOrderId) {
            throw new LocalizedException(__('Cannot initialize order for quote with id = "%1"', $quote->getId()));
        }
        if ($quote->getCustomer()->getId() && !isset($orderData['data']['application_state']['customer']['public_id'])) {
            throw new LocalizedException(__('Cannot authenticate customer with id="%1"', $quote->getCustomerId()));
        }

        $this->setQuoteExtensionData->execute((int)$quote->getId());

        foreach ($this->orderDataProcessors as $processor) {
            $orderData = $processor->process($orderData, $quote);
        }

        return $orderData;
    }

    /**
     * Retrieve customer addresses countries.
     *
     * @param CartInterface $quote
     * @return Country[]
     */
    private function getCustomerCountries(CartInterface $quote): array
    {
        $countryCollection = $this->countryCollectionFactory->create();
        $countryIds = [];
        foreach ($quote->getCustomer()->getAddresses() as $address) {
            $countryIds[] = $address->getCountryId();
        }
        if (!$countryIds) {
            return [];
        }
        return $countryCollection->addFieldToFilter('country_id', $countryIds)->getItems();
    }

    /**
     * Get country name by address country id.
     *
     * @param Country[] $countries
     * @param AddressInterface $address
     * @return string
     * @throws LocalizedException
     */
    private function getCountryNameForAddress(array $countries, AddressInterface $address): string
    {
        foreach ($countries as $country) {
            if ($country->getCountryId() === $address->getCountryId()) {
                return $country->getName();
            }
        }
        throw new LocalizedException(
            __(
                'Cannot find country name for customer "%s" address "%s".',
                $address->getCustomerId(), $address->getId()
            )
        );
    }

    /**
     * Get address payload.
     *
     * @param AddressInterface $address
     * @param array $countries
     * @return array
     * @throws LocalizedException
     */
    private function getAddress(AddressInterface $address, array $countries): array
    {
        return [
            'first_name' => (string)$address->getFirstname(),
            'last_name' => (string)$address->getLastname(),
            'address_line_1' => (string)($address->getStreet()[0] ?? ''),
            'address_line_2' => (string)($address->getStreet()[1] ?? ''),
            'country' => $this->getCountryNameForAddress($countries, $address),
            'city' => (string)$address->getCity(),
            'province' => (string)$address->getRegion()->getRegion(),
            'country_code' => (string)$address->getCountryId(),
            'province_code' => (string)$address->getRegion()->getRegionCode(),
            'postal_code' => (string)$address->getPostcode(),
            'business_name' => (string)$address->getCompany(),
            'phone_number' => (string)$address->getTelephone(),
        ];
    }
}

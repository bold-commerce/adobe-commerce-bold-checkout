<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Quote\GetCartLineItems;
use Bold\Checkout\Model\Quote\QuoteAction;
use Exception;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

/**
 * Init order data from quote on Bold side.
 */
class InitOrderFromQuote
{
    private const INIT_URL = '/checkout/orders/{{shopId}}/init';

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
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

     /**
      * @param ClientInterface $client
      * @param CollectionFactory $countryCollectionFactory
      * @param GetCartLineItems $getCartLineItems
      * @param QuoteAction $quoteAction
      * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
      */
    public function __construct(
        ClientInterface $client,
        CollectionFactory $countryCollectionFactory,
        GetCartLineItems $getCartLineItems,
        QuoteAction $quoteAction,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->client = $client;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->getCartLineItems = $getCartLineItems;
        $this->quoteAction = $quoteAction;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * Initialize order on bold side.
     *
     * @param CartInterface $quote
     * @return array
     * @throws Exception
     */
    public function init(CartInterface $quote): array
    {
        $maskedQuoteId = $quote->getCustomerIsGuest() ? $this->quoteIdToMaskedQuoteId->execute((int) $quote->getId()) : null;
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $actions = $this->quoteAction->getActionsData($quote);
        $mergedActions = array_reduce($actions, function($carry, $item) {
            return array_merge($carry, $item);
        }, []);

        $body = [
            'cart_items' => $this->getCartLineItems->getItems($quote),
            'actions' => $mergedActions,
            'order_meta_data' => [
                'cart_parameters' => [
                    'quote_id' => $quote->getId(),
                    'masked_quote_id' => $maskedQuoteId,
                    'store_id' => $quote->getStoreId(),
                    'website_id' => $websiteId,
                ]
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

        $orderData = $this->client->call($websiteId, 'POST', self::INIT_URL, $body)->getBody();
        $publicOrderId = $orderData['data']['public_order_id'] ?? null;
        if (!$publicOrderId) {
            throw new LocalizedException(__('Cannot initialize order for quote with id = "%s"', $quote->getId()));
        }

        if ($quote->getCustomer()->getId() && !isset($orderData['data']['application_state']['customer']['public_id'])) {
            throw new LocalizedException(__('Cannot authenticate customer with id="%s"', $quote->getCustomerId()));
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

<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\Address;

use Magento\Directory\Model\CountryFactory;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Quote address to bold address converter.
 */
class Converter
{
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    public function __construct(CountryFactory $countryFactory)
    {
        $this->countryFactory = $countryFactory;
    }

    /**
     * Convert order address to array.
     *
     * @param OrderAddressInterface $address
     * @return array
     */
    public function convert(OrderAddressInterface $address): array
    {
        $country = $this->countryFactory->create()->loadByCode($address->getCountryId());
        return [
            'id' => (int)$address->getId() ?: null,
            'business_name' => (string)$address->getCompany(),
            'country_code' => (string)$address->getCountryId(),
            'country' => (string)$country->getName(),
            'city' => (string)$address->getCity(),
            'first_name' => (string)$address->getFirstname(),
            'last_name' => (string)$address->getLastname(),
            'phone_number' => (string)$address->getTelephone(),
            'postal_code' => (string)$address->getPostcode(),
            'province' => (string)$address->getRegion(),
            'province_code' => (string)$address->getRegionCode(),
            'address_line_1' => (string)($address->getStreet()[0] ?? ''),
            'address_line_2' => (string)($address->getStreet()[1] ?? ''),
        ];
    }
}

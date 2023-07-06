<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Address;

use Magento\Quote\Api\Data\AddressInterface;

/**
 * Quote address to bold address converter.
 */
class Converter
{
    /**
     * Convert quote address to array.
     *
     * @param AddressInterface $address
     * @return array
     */
    public function convert(AddressInterface $address)
    {
        return [
            'id' => (int)$address->getId() ?: null,
            'business_name' => (string)$address->getCompany(),
            'country_code' => (string)$address->getCountryId(),
            'country' => (string)$address->getCountryModel()->getName(),
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

<?php

namespace Bold\Checkout\Api;

use Magento\Directory\Api\Data\CountryInformationInterface;

interface GetCountryDataInterface
{
    /**
     * @param string $countryId
     * @return \Magento\Directory\Api\Data\CountryInformationInterface
     */
    public function getData(string $countryId): CountryInformationInterface;
}

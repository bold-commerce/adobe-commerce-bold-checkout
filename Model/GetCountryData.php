<?php

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\GetCountryDataInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;
use Magento\Directory\Api\Data\CountryInformationInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetCountryData implements GetCountryDataInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var CountryInformationInterfaceFactory
     */
    private $countryInformationFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        CountryInformationInterfaceFactory $countryInformationFactory
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->countryInformationFactory = $countryInformationFactory;
    }

    public function getData(string $countryId): CountryInformationInterface
    {
        $storeId = $this->storeManager->getStore()->getId();
        $countriesRequiringStates = $this->scopeConfig->getValue('general/region/state_required', ScopeInterface::SCOPE_STORE, $storeId);
        $statesOptional = !in_array($countryId, explode(',', $countriesRequiringStates));
        $countryData = $this->countryInformationAcquirer->getCountryInfo($countryId);

        if ($statesOptional) {
            $customCountryData = $this->countryInformationFactory->create();
            $customCountryData->setId($countryId);
            $customCountryData->setTwoLetterAbbreviation($countryData->getTwoLetterAbbreviation());
            $customCountryData->setThreeLetterAbbreviation($countryData->getThreeLetterAbbreviation());
            $customCountryData->setFullNameLocale($countryData->getFullNameLocale());
            $customCountryData->setFullNameEnglish($countryData->getFullNameEnglish());

            return $customCountryData;
        }

        return $countryData;
    }
}

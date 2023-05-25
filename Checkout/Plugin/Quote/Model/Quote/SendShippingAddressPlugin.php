<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Model\Quote;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

/**
 * Send shipping address to Bold plugin.
 */
class SendShippingAddressPlugin
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Converter
     */
    private $addressConverter;

    /**
     * @param ClientInterface $client
     * @param Session $session
     * @param Converter $addressConverter
     */
    public function __construct(
        ClientInterface $client,
        Session $session,
        Converter $addressConverter
    ) {
        $this->client = $client;
        $this->session = $session;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Send billing address to Bold.
     *
     * @param Quote $subject
     * @param Quote $result
     * @param AddressInterface|null $address
     * @return Quote
     */
    public function afterSetShippingAddress(
        Quote $subject,
        Quote $result,
        AddressInterface $address = null
    ): Quote {
        if (!$this->canSendAddress($address)) {
            return $result;
        }
        $addressData = $this->addressConverter->convert($address);
        try {
            $setAddressResult = $this->client->post(
                (int)$subject->getStore()->getWebsiteId(),
                'addresses/shipping',
                $addressData
            );
            if ($setAddressResult->getErrors()) {
                $this->session->setBoldCheckoutData(null);
            }
        } catch (\Exception $e) {
            $this->session->setBoldCheckoutData(null);
            return $result;
        }
        return $result;
    }

    /**
     * Verify if address can be sent to Bold.
     *
     * @param AddressInterface|null $address
     * @return bool
     */
    public function canSendAddress(?AddressInterface $address): bool
    {
        if (!$address) {
            return false;
        }
        if (!$this->session->getBoldCheckoutData()) {
            return false;
        }
        if (!$address->getCountryId() || !$address->getFirstname() || !$address->getLastname()) {
            return false;
        }
        return true;
    }
}

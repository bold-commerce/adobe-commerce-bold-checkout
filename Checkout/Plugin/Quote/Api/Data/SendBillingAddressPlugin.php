<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Api\Data;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Send billing address to Bold.
 */
class SendBillingAddressPlugin
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
     * @param CartInterface $subject
     * @param CartInterface $result
     * @param AddressInterface|null $address
     * @return CartInterface
     */
    public function afterSetBillingAddress(
        CartInterface $subject,
        CartInterface $result,
        AddressInterface $address = null
    ): CartInterface {
        if (!$address || !$this->session->getBoldCheckoutData()) {
            return $result;
        }
        $addressData = $this->addressConverter->convert($address);
        try {
            $setAddressResult = $this->client->post(
                (int)$subject->getStore()->getWebsiteId(),
                'addresses/billing',
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
}

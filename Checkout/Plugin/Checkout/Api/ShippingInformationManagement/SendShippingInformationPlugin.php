<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Checkout\Api\ShippingInformationManagement;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Quote\Address\Converter;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Send shipping information to Bold.
 */
class SendShippingInformationPlugin
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Converter
     */
    private $addressConverter;

    /**
     * @param Session $session
     * @param Converter $addressConverter
     * @param ClientInterface $client
     */
    public function __construct(Session $session, Converter $addressConverter, ClientInterface $client)
    {
        $this->session = $session;
        $this->client = $client;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Send shipping information to Bold.
     *
     * @param ShippingInformationManagementInterface $subject
     * @param PaymentDetailsInterface $result
     * @return PaymentDetailsInterface
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        PaymentDetailsInterface $result
    ) {
        $boldCheckoutData = $this->session->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return $result;
        }
        try {
            $quote = $this->session->getQuote();
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $addressPayload = $this->addressConverter->convert($quote->getShippingAddress());
            $existingAddress = $boldCheckoutData['data']['application_state']['addresses']['shipping'] ?? [];
            if (!$this->isAddressSynced($existingAddress, $addressPayload)) {
                $this->client->post($websiteId, 'addresses/shipping', $addressPayload);
            }
            $boldCheckoutData = $this->session->getBoldCheckoutData();
            $shipping = $boldCheckoutData['data']['application_state']['shipping']['selected_shipping']['code'] ?? null;
            if ($shipping === $quote->getShippingAddress()->getShippingMethod()) {
                return $result;
            }
            $this->sendShippingMethodIndex($quote->getShippingAddress()->getShippingMethod());
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * Send selected shipping method index to Bold.
     *
     * @param string $shippingMethod
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function sendShippingMethodIndex(string $shippingMethod): void
    {
        $boldCheckoutData = $this->session->getBoldCheckoutData();
        $lines = $boldCheckoutData['data']['application_state']['shipping']['available_shipping_lines'] ?? [];
        $websiteId = (int)$this->session->getQuote()->getStore()->getWebsiteId();
        foreach ($lines as $line) {
            if ($line['code'] === $shippingMethod) {
                $this->client->post(
                    $websiteId,
                    'shipping_lines',
                    ['index' => $line['id']]
                );
                return;
            }
        }
        $shippingLines = $this->getShippingLines();
        foreach ($shippingLines as $shippingLine) {
            if ($shippingLine['code'] === $shippingMethod) {
                $this->client->post(
                    $websiteId,
                    'shipping_lines',
                    ['index' => $shippingLine['id']]
                );
                return;
            }
        }
    }

    /**
     * Get shipping lines from Bold.
     *
     * @return array
     */
    private function getShippingLines(): array
    {
        try {
            $websiteId = (int)$this->session->getQuote()->getStore()->getWebsiteId();
            $lines = $this->client->get($websiteId, 'shipping_lines');
            if ($lines->getErrors()) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        return $lines->getBody()['data']['shipping_lines'];
    }

    /**
     * Verify if the shipping address is the same as the one sent to Bold.
     *
     * @param array $existingAddress
     * @param array $addressPayload
     * @return bool
     */
    public function isAddressSynced(array $existingAddress, array $addressPayload): bool
    {
        $addressPayload['id'] = $existingAddress['id'] ?? null;
        ksort($existingAddress);
        ksort($addressPayload);
        return $existingAddress === $addressPayload;
    }
}

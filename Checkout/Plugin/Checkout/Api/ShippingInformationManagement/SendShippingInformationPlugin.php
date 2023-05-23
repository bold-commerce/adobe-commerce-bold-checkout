<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Checkout\Api\ShippingInformationManagement;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @param Session $session
     * @param ClientInterface $client
     */
    public function __construct(Session $session, ClientInterface $client)
    {
        $this->session = $session;
        $this->client = $client;
    }

    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        PaymentDetailsInterface $result
    ) {
        $quote = $this->session->getQuote();
        if (!$this->session->getBoldCheckoutData()) {
            return $result;
        }
        try {
            //todo: replace address::getShippingDescription with address::getShippingMethod after
            // https://trello.com/c/TRcLOQQg/56-waiting-on-bold-shipping-lines-have-no-codes will be fixed.
            $this->sendShippingMethodIndex($quote->getShippingAddress()->getShippingDescription());
            $taxes = $this->client->post((int)$quote->getStore()->getWebsiteId(), 'taxes', []);
            if ($taxes->getErrors()) {
                $this->session->setBoldCheckoutData(null);
                return $result;
            }
        } catch (\Exception $e) {
            $this->session->setBoldCheckoutData(null);
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
        $shippingLines = $this->getShippingLines();
        foreach ($shippingLines as $shippingLine) {
            if ($shippingLine['code'] === $shippingMethod) {
                $shippingLine = $this->client->post(
                    (int)$this->session->getQuote()->getStore()->getWebsiteId(),
                    'shipping_lines',
                    ['index' => $shippingLine['id']]
                );
                if ($shippingLine->getErrors()) {
                    throw new LocalizedException(__('Unable to save shipping method.'));
                }
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
            $lines = $this->client->get(
                (int)$this->session->getQuote()->getStore()->getWebsiteId(),
                'shipping_lines'
            );
            if ($lines->getErrors()) {
                $this->session->setBoldCheckoutData(null);
                return [];
            }
        } catch (\Exception $e) {
            $this->session->setBoldCheckoutData(null);
            return [];
        }
        return $lines->getBody()['data']['shipping_lines'];
    }
}

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
        if (!$this->session->getBoldCheckoutData()) {
            return $result;
        }
        try {
            $quote = $this->session->getQuote();
            //todo: replace address::getShippingDescription with address::getShippingMethod after
            // https://trello.com/c/TRcLOQQg/56-waiting-on-bold-shipping-lines-have-no-codes will be fixed.
            $this->sendShippingMethodIndex($quote->getShippingAddress()->getShippingDescription());
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
        $websiteId = (int)$this->session->getQuote()->getStore()->getWebsiteId();
        $shippingLines = $this->getShippingLines();
        foreach ($shippingLines as $shippingLine) {
            if ($shippingLine['code'] === $shippingMethod) {
                $shippingLine = $this->client->post(
                    $websiteId,
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
            $websiteId = (int)$this->session->getQuote()->getStore()->getWebsiteId();
            $lines = $this->client->get(
                $websiteId,
                'shipping_lines'
            );
            if ($lines->getErrors()) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
        return $lines->getBody()['data']['shipping_lines'];
    }
}

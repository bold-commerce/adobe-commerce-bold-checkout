<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client\Request\Validator;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validate if Place Order payload has all required data set.
 */
class OrderPayloadValidator
{
    /**
     * @var array
     */
    private $requiredProperties;

    /**
     * @param array $requiredProperties
     */
    public function __construct(array $requiredProperties = [])
    {
        $this->requiredProperties = $requiredProperties;
    }

    /**
     * Validate if Place Order payload has all required data set.
     *
     * @param OrderDataInterface $orderData
     * @return void
     * @throws LocalizedException
     */
    public function validate(OrderDataInterface $orderData): void
    {
        foreach ($this->requiredProperties as $property) {
            $methodName = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
            if (method_exists($orderData, $methodName) && ($orderData->$methodName() === null)) {
                throw new LocalizedException(
                    __('Order creation service requires "%1" to be set in payload.', $property)
                );
            }
        }
    }
}

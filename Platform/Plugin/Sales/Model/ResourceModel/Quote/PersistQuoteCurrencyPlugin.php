<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Sales\Model\ResourceModel\Quote;

use Magento\Directory\Model\Currency;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Persist quote currency for rest api.
 */
class PersistQuoteCurrencyPlugin
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Set quote currency as forced currency to prevent set one as base store currency for rest api.
     *
     * @param Quote $subject
     * @param AbstractModel $quote
     * @return void
     */
    public function beforeSave(Quote $subject, AbstractModel $quote): void
    {
        $currency = $this->currency->load($quote->getQuoteCurrencyCode());
        $quote->setForcedCurrency($currency);
    }
}

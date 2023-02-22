<?php
declare(strict_types=1);

namespace Bold\Checkout\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Bold Payment info block.
 */
class Info extends ConfigurableInfo
{
    /**
     * Convert label into phrase.
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field): Phrase
    {
        return __($field);
    }
}

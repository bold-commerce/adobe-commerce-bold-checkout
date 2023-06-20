<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Plugin\Framework\App\Router;

use Bold\CheckoutSelfHosted\Controller\Index\Index;
use Bold\CheckoutSelfHosted\Controller\Index\IndexNoCsp;
use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Framework\App\Router\ActionList;

/**
 * Create index controller instance considering CSP support plugin.
 */
class ActionListPlugin
{
    /**
     * Create index controller instance considering CSP support.
     *
     * @param ActionList $subject
     * @param string $result
     * @return string
     */
    public function afterGet(
        ActionList $subject,
        string $result
    ) {
        if ($result !== Index::class) {
            return $result;
        }
        return \interface_exists(CspAwareActionInterface::class) ? Index::class : IndexNoCsp::class;
    }
}

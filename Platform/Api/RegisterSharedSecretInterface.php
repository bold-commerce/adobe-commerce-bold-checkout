<?php
declare(strict_types=1);

namespace Bold\Platform\Api;

use Bold\Platform\Api\Data\RegisterSharedSecret\ResultInterface;

/**
 * Add shared secret for m2 bold integration auth.
 */
interface RegisterSharedSecretInterface
{
    /**
     * Add shared secret to authorize outgoing requests to bold m2 integration.
     *
     * @param string $shopId
     * @param string $sharedSecret
     * @return \Bold\Platform\Api\Data\RegisterSharedSecret\ResultInterface
     */
    public function register(string $shopId, string $sharedSecret): ResultInterface;
}

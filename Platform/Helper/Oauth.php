<?php

declare(strict_types=1);

namespace Bold\Platform\Helper;

/**
 * Helper to provide non-random Token and Secret for Integration creation.
 */
class Oauth extends \Magento\Framework\Oauth\Helper\Oauth
{
    private $token = null;
    private $tokenSecret = null;

    /**
     * Return a saved token instead of randomly generated.
     *
     * @return string
     */
    public function generateToken()
    {
        return $this->token;
    }

    /**
     * Return a saved secret instead of randomly generated.
     *
     * @return string
     */
    public function generateTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * Set the pre-generated token.
     *
     * @param string $token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Set the pre-generated secret.
     *
     * @param string $tokenSecret
     *
     * @return void
     */
    public function setTokenSecret(string $tokenSecret): void
    {
        $this->tokenSecret = $tokenSecret;
    }
}

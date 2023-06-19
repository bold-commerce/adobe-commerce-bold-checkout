<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client\Command\Client;

use Magento\Framework\HTTP\Client\Curl as CurlCore;

/**
 * Bold Checkout Curl client.
 */
class Curl extends CurlCore
{
    /**
     * Perform delete request.
     *
     * @param string $uri
     * @param string $params
     */
    public function delete(string $uri, string $params): void
    {
        $this->makeRequest('DELETE', $uri, $params);
    }

    /**
     * Perform put request.
     *
     * @param string $uri
     * @param string $params
     */
    public function put(string $uri, string $params): void
    {
        $this->makeRequest('PUT', $uri, $params);
    }

    /**
     * Perform patch request.
     *
     * @param string $url
     * @param string $params
     */
    public function patch(string $url, string $params): void
    {
        $this->makeRequest('PATCH', $url, $params);
    }

    /**
     * @inheritDoc
     */
    protected function makeRequest($method, $uri, $params = [])
    {
        $this->_ch = curl_init();
        $this->curlOption(CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS | CURLPROTO_FTP | CURLPROTO_FTPS);
        $this->curlOption(CURLOPT_URL, $uri);
        $this->curlOption(CURLOPT_CUSTOMREQUEST, $method);
        $this->curlOption(CURLOPT_POSTFIELDS, $params);
        if (count($this->_headers)) {
            $heads = [];
            foreach ($this->_headers as $k => $v) {
                $heads[] = $k . ': ' . $v;
            }
            $this->curlOption(CURLOPT_HTTPHEADER, $heads);
        }
        if (count($this->_cookies)) {
            $cookies = [];
            foreach ($this->_cookies as $k => $v) {
                $cookies[] = "{$k}={$v}";
            }
            $this->curlOption(CURLOPT_COOKIE, implode(';', $cookies));
        }
        if ($this->_timeout) {
            $this->curlOption(CURLOPT_TIMEOUT, $this->_timeout);
        }
        if ($this->_port != 80) {
            $this->curlOption(CURLOPT_PORT, $this->_port);
        }
        $this->curlOption(CURLOPT_RETURNTRANSFER, 1);
        $this->curlOption(CURLOPT_HEADERFUNCTION, [$this, 'parseHeaders']);
        if (count($this->_curlUserOptions)) {
            foreach ($this->_curlUserOptions as $k => $v) {
                $this->curlOption($k, $v);
            }
        }
        $this->_headerCount = 0;
        $this->_responseHeaders = [];
        $this->_responseBody = curl_exec($this->_ch);
        $err = curl_errno($this->_ch);
        if ($err) {
            $this->doError(curl_error($this->_ch));
        }
        curl_close($this->_ch);
    }
}

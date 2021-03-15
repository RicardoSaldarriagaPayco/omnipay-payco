<?php
namespace Omnipay\Payco\Messages;

use Omnipay\Common\Message\AbstractRequest;

class AccessTokenRequest extends RestTokenRequest
{
    public function getData()
    {
        return array('grant_type' => 'client_credentials');
    }
    public function sendData($data)
    {
        $body = $data ? http_build_query($data, '', '&') : null;
        $httpResponse = $this->httpClient->request(
            'POST',
            'https://api.secure.payco.co/v1/auth/login',
            array(
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->getApiKey()}:{$this->getPrivateKey()}"),
            ),
            $body
        );
        // Empty response body should be parsed also as and empty array
        $body = (string) $httpResponse->getBody()->getContents();
        $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
        return $this->response = new RestResponse($this, $jsonToArrayResponse, $httpResponse->getStatusCode());
    }
}
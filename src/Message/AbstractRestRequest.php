<?php

namespace Omnipay\Payco\Message;

use Omnipay\Common\Exception\InvalidResponseException;


abstract class AbstractRestRequest extends \Omnipay\Common\Message\AbstractRequest
{

   /**
     * Test Endpoint URL
     *
     * @var string URL
     */
    protected $BASE_URL = 'https://api.secure.payco.co';

    /**
     * Live Endpoint URL
     *
     * @var string URL
     */
    protected $BASE_URL_SECURE = 'https://secure.payco.co';

    /**
     * Payco Payer ID
     *
     * @var string PayerID
     */
    protected $payerId = null;


    /**
     * credentials.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getPrivateKey()
    {
        return $this->getParameter('privateKey');
    }

    public function setPrivateKey($value)
    {
        return $this->setParameter('privateKey', $value);
    }


    public function getToken()
    {
        return $this->getParameter('token');
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    /**
     * Get HTTP Method.
     *
     * This is nearly always POST but can be over-ridden in sub classes.
     *
     * @return string
     */
    protected function getHttpMethod()
    {
        return 'POST';
    }

    protected function getEndpoint($url, $switch=null)
    {
        if($switch){
            return $this->BASE_URL_SECURE. '/' .$url;
        }else{
            return $this->BASE_URL. '/' .$url;
        }
    }

    public function sendData($data)
    {}

    /**
     * Returns object JSON
     * @param int $options http://php.net/manual/en/json.constants.php
     * @return string
     */
    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }

    protected function createResponse($data, $statusCode)
    {
        return $this->response = new Response($this, $data, $statusCode);
    }
}

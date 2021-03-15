<?php

namespace Omnipay\Payco;

use Omnipay\Common\AbstractGateway;


class Gateway extends AbstractGateway
{
    const BASE_URL = 'https://api.secure.payco.co';
    const BASE_URL_SECURE = 'https://secure.payco.co';

    public function getName()
    {
        return 'Payco';
    }
    public function getDefaultParameters()
    {
        return array(
            'apiKey'     => '',
            'privateKey'       => '',
            'lang'        => '',
            'test'     => true,
        );
    }

    
    /**
     * Get OAuth 2.0 client ID for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your ApiKey:secret as your Basic Auth
     * credentials.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('ApiKey');
    }

    /**
     * Set OAuth 2.0 client ID for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your ApiKey:secret as your Basic Auth
     * credentials.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setApiKey($value)
    {
        return $this->setParameter('ApiKey', $value);
    }

    /**
     * Get OAuth 2.0 client ID for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your ApiKey:secret as your Basic Auth
     * credentials.
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getParameter('PrivateKey');
    }

    /**
     * Set OAuth 2.0 client ID for the access token.
     *
     * Get an access token by using the OAuth 2.0 client_credentials
     * token grant type with your PrivateKey:secret as your Basic Auth
     * credentials.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setPrivateKey($value)
    {
        return $this->setParameter('PrivateKey', $value);
    }

     /**
     * Get OAuth 2.0 access token.
     *
     * @param bool $createIfNeeded [optional] - If there is not an active token present, should we create one?
     * @return string
     */
    public function getToken($createIfNeeded = true)
    {
        if ($createIfNeeded && !$this->hasToken()) {
            $response = $this->createToken()->send();
            if ($response->isSuccessful()) {
                $data = $response->getData();
                if (isset($data['bearer_token'])) {
                    $this->setToken($data['bearer_token']);
                    //$this->setTokenExpires(time() + $data['expires_in']);
                }
            }
        }

        return $this->getParameter('bearer_token');
    }

        /**
     * Set OAuth 2.0 access token.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setToken($value)
    {
        return $this->setParameter('bearer_token', $value);
    }

    /**
     * Create OAuth 2.0 access token request.
     *
     * @return \Omnipay\Payco\Message\RestTokenRequest
     */
    public function createToken()
    {
        return $this->createRequest('\Omnipay\Payco\Message\RestTokenRequest', array());
    }

    
   /**
     * Create Request
     *
     * This overrides the parent createRequest function ensuring that the OAuth
     * 2.0 access token is passed along with the request data -- unless the
     * request is a RestTokenRequest in which case no token is needed.  If no
     * token is available then a new one is created (e.g. if there has been no
     * token request or the current token has expired).
     *
     * @param string $class
     * @param array $parameters
     * @return \Omnipay\Payco\Message\AbstractRestRequest
     */
    public function createRequest($class, array $parameters = array())
    {
            // This will set the internal token parameter which the parent
            // createRequest will find when it calls getParameters().
        $this->getToken(true);
        return parent::createRequest($class, $parameters);
    }

     /**
     * Create a purchase request.
     *
     * Payco provides various payment related operations using the /payment
     * resource and related sub-resources. Use payment for direct credit card
     * payments and Payco account payments. You can also use sub-resources
     * to get payment related details.
     *
     * @link https://developer.paypal.com/docs/api/#create-a-payment
     * @param array $parameters
     * @return \Omnipay\Payco\Message\RestPurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\RestPurchaseRequest', $parameters);
    }
}
<?php
namespace Omnipay\Payco;

use Omnipay\Common\AbstractGateway;

/**
 * Payco Gateway
 *
 * This gateway is useful for testing. It implements all the functions listed in \Omnipay\Common\GatewayInterface
 * and allows both successful and failed responses based on the input.
 *
 * For authorize(), purchase(), and createCard() functions ...
 *
 *    Any card number which passes the Luhn algorithm and ends in an even number is authorized,
 *    for example: 4242424242424242
 *
 *    Any card number which passes the Luhn algorithm and ends in an odd number is declined,
 *    for example: 4111111111111111
 *
 * For capture(), completeAuthorize(), completePurchase(), refund(), and void() functions...
 *    A transactionReference option is required. If the transactionReference contains 'fail', the
 *    request fails. For any other values, the request succeeds
 *
 * For updateCard() and deleteCard() functions...
 *    A cardReference field is required. If the cardReference contains 'fail', the
 *    request fails. For all other values, it succeeds.
 *
 * ### Example
 * <code>
 * // Create a gateway for the Payco Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('Payco');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'testMode' => true, // Doesn't really matter what you use here.
 * ));
 *
 * // Create a credit card object
 * // This card can be used for testing.
 * $card = new CreditCard(array(
 *             'firstName'    => 'Example',
 *             'lastName'     => 'Customer',
 *             'number'       => '4242424242424242',
 *             'expiryMonth'  => '01',
 *             'expiryYear'   => '2020',
 *             'cvv'          => '123',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'amount'                   => '10.00',
 *     'currency'                 => 'AUD',
 *     'card'                     => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Payco';
    }

    public function getDefaultParameters()
    {
        return array(
            'apiKey'     => '',
            'privateKey'       => '',
            'lenguage'        => '',
            'testMode'     => false,
        );
    }

    /**
     * credentials.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * credentials.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * credentials.
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getParameter('privateKey');
    }

    /**
     * credentials.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setPrivateKey($value)
    {
        return $this->setParameter('privateKey', $value);
    }

        /**
     * credentials.
     *
     * @return string
     */
    public function getLenguage()
    {
        return $this->getParameter('lenguage');
    }

    /**
     * credentials.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setLenguage($value)
    {
        return $this->setParameter('lenguage', $value);
    }

     /**
     * Get OAuth 2.0 access token.
     *
     * @param bool $createIfNeeded [optional] - If there is not an active token present, should we create one?
     * @return string
     */
    public function getToken($createIfNeeded = true)
    {
        if ($createIfNeeded) {
            $response = $this->createToken()->send();
            if ($response->isSuccessful()) {
                $data = $response->getData();
                if (isset($data['bearer_token'])) {
                    $this->setToken($data['bearer_token']);
                    // $this->setTokenExpires(time() + $data['expires_in']);
                }
            }
        }

        return $this->getParameter('token');
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
     * Set OAuth 2.0 access token.
     *
     * @param string $value
     * @return Gateway provides a fluent interface
     */
    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    
    /**
     * Create Request
     *
     * @param string $class
     * @param array $parameters
     */
    public function createRequest($class, array $parameters = array())
    {
        if ($class != '\Omnipay\Payco\Message\RestTokenRequest') {
            $this->getToken(true);
        }
        return parent::createRequest($class, $parameters);
    }
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\RestAuthorizeRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\CreditCardRequest', $parameters);
    }

    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\TransactionReferenceRequest', $parameters);
    }

    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\TransactionReferenceRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\TransactionReferenceRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\TransactionReferenceRequest', $parameters);
    }

    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\TransactionReferenceRequest', $parameters);
    }

    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\CreditCardRequest', $parameters);
    }

    public function updateCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\CardReferenceRequest', $parameters);
    }

    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Payco\Message\CardReferenceRequest', $parameters);
    }
}

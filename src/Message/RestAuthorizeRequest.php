<?php
/**
 * Payco Authorize Request
 */

namespace Omnipay\Payco\Message;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

/**
 * "PayPal failed to process the transaction from your card. For privacy reasons,
 * PayPal are unable to disclose to us the reason for this failure. You should try
 * a different payment method, a different card within PayPal, or contact PayPal
 * support if you need to understand the reason for the failed transaction. PayPal
 * may advise you to use a different card if the particular card is rejected
 * by the card issuer."
 *
 * @link https://developer.paypal.com/docs/integration/direct/capture-payment/#authorize-the-payment
 * @link https://developer.paypal.com/docs/api/#authorizations
 * @link http://bit.ly/1wUQ33R
 * @see RestCaptureRequest
 * @see RestPurchaseRequest
 */
class RestAuthorizeRequest extends AbstractRequest
{
    public function getData()
    {
        return $this->getParameters();
    }


    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => $data['bearer_token']
        ];
        $apiUrl = $data['apiUrl'] . '/api/v2_1/orders';
        if (isset($data['purchaseData']['extOrderId'])) {
            $this->setTransactionId($data['purchaseData']['extOrderId']);
        }
        $httpRequest = $this->httpClient->post($apiUrl, $headers, json_encode($data['purchaseData']));
        $httpRequest->configureRedirects(true, 0);
        $httpResponse = $httpRequest->send();
        $responseData = $httpResponse->json();
        $response = new PurchaseResponse($this, $responseData);

        return $this->response = $response;
    }
}

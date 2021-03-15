<?php
/**
 * Payco Purchase Request
 */

namespace Omnipay\Payco\Message;

/**
 * "PayPal failed to process the transaction from your card. For privacy reasons,
 * PayPal are unable to disclose to us the reason for this failure. You should try
 * a different payment method, a different card within PayPal, or contact PayPal
 * support if you need to understand the reason for the failed transaction. PayPal
 * may advise you to use a different card if the particular card is rejected
 * by the card issuer."
 *
 * @link https://developer.paypal.com/docs/api/#create-a-payment
 * @see RestAuthorizeRequest
 */
class RestPurchaseRequest extends RestAuthorizeRequest
{
    public function getData()
    {
        $data = parent::getData();
        return $data;
    }
}

<?php
namespace Omnipay\Payco\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;

/**
 * Payco Authorize/Purchase Request
 *
 * This is the request that will be called for any transaction which submits a credit card,
 * including `authorize` and `purchase`
 */
class CreditCardRequest extends AbstractRestRequest
{

    public function getToken()
    {
        return $this->getParameter('token');
    }

    public function setToken($value)
    {
        return $this->setParameter('token', $value);
    }

    public function getData()
    {
            $data = array(
                'intent' => 'authorize',
                'payer' => array(
                    'payment_method' => 'credit_card',
                    'funding_instruments' => array()
                ),
                'test' => $this->getTestMode(),
                'transactions' => array(
                    array(
                        'description' => $this->getDescription(),
                        'amount' => array(
                            'total' => $this->getAmount(),
                            'currency' => $this->getCurrency(),
                        ),
                        'invoice_number' => $this->getTransactionId()
                    )
                )
            );
    
            $items = $this->getItems();
            if ($items) {
                $itemList = array();
                foreach ($items as $n => $item) {
                    $itemList[] = array(
                        'name' => $item->getName(),
                        'description' => $item->getDescription(),
                        'quantity' => $item->getQuantity(),
                        'price' => $this->formatCurrency($item->getPrice()),
                        'currency' => $this->getCurrency()
                    );
                }
                $data['transactions'][0]['item_list']["items"] = $itemList;
            }
    
            if ($this->getCardReference()) {
                $this->validate('amount');
                $data['payer']['funding_instruments'][] = array(
                    'credit_card_token' => array(
                        'credit_card_id' => $this->getCardReference(),
                    ),
                );
            } elseif ($this->getCard()) {
                $this->validate('amount', 'card');
                $this->getCard()->validate();
                $data['payer']['funding_instruments'][] = array(
                    'credit_card' => array(
                        'number' => $this->getCard()->getNumber(),
                        'type' => $this->getCard()->getBrand(),
                        'expire_month' => $this->getCard()->getExpiryMonth(),
                        'expire_year' => $this->getCard()->getExpiryYear(),
                        'cvv2' => $this->getCard()->getCvv(),
                        'first_name' => $this->getCard()->getFirstName(),
                        'last_name' => $this->getCard()->getLastName(),
                        'billing_address' => array(
                            'line1' => $this->getCard()->getAddress1(),
                            //'line2' => $this->getCard()->getAddress2(),
                            'city' => $this->getCard()->getCity(),
                            'state' => $this->getCard()->getState(),
                            'postal_code' => $this->getCard()->getPostcode(),
                            'country_code' => strtoupper($this->getCard()->getCountry()),
                        )
                    )
                );
    
                $line2 = $this->getCard()->getAddress2();
                if (!empty($line2)) {
                    $data['payer']['funding_instruments'][0]['credit_card']['billing_address']['line2'] = $line2;
                }
            } else {
                $this->validate('amount', 'returnUrl', 'cancelUrl');
                unset($data['payer']['funding_instruments']);
                $data['payer']['payment_method'] = 'payco';
                $data['redirect_urls'] = array(
                    'return_url' => $this->getReturnUrl(),
                    'cancel_url' => $this->getCancelUrl(),
                );
            }
            return $data;
    }

    public function sendData($data)
    {
        var_dump($data);
        die();
        $jsonToArrayResponse=$this->getCustomer();
        $data['customer_id']=$jsonToArrayResponse["customer_id"];
        $data['token_id']=$jsonToArrayResponse["token_id"];
        $data['reference'] = uniqid();
        $data['success'] = 0 === substr($this->getCard()->getNumber(), -1, 1) % 2;
        $data['message'] = $data['success'] ? 'Success' : 'Failure';
        // $data['customer_id']="neDDwtKtPbwe2XAeq";
        // $data['token_id']="ucNSjvqtwJpoHiCyS";

         $pay = array(
            "customer_id" => $data["customer_id"],
            "token_card" => $data["token_id"],
            "doc_type" => "CC",
            "doc_number" => "12312312311",
            "name" => $this->getCard()->getFirstName(),
            "last_name" => $this->getCard()->getLastName(),
            "email" => $this->getCard()->getEmail(),
            "bill" => $data["transactions"][0]['invoice_number'],
            "description" => $data["transactions"][0]['description'],
            "value" => $data["transactions"][0]['amount']["total"],
            "tax" => "0",
            "tax_base" =>$data["transactions"][0]['amount']["total"],
            "currency" => $data["transactions"][0]['amount']["currency"],
            "dues" => "12",
            "address" => $this->getCard()->getAddress1(),
            "url_response" => "https://tudominio.com/confirmacion.php",
            "url_confirmation" => "https://tudominio.com/confirmacion.php",
            "use_default_card_customer" => true,
            "extras"=> array(
                "extra1" => "omnipay"
            )
        );
        $body = $pay ? json_encode($pay): null;

        $headers = array("Content-Type" => "application/json", "Accept" => "application/json", "Type" => 'sdk-jwt', "lang" => "PHP",  'Authorization' => 'Bearer ' . $this->getToken());
        try {
            $httpResponse = $this->httpClient->request(
                $this->getHttpMethod(),
                $this->getEndpoint('payment/v1/charge/create'),
                $headers,
                $body
            );
            // Empty response body should be parsed also as and empty array
            $body = (string) $httpResponse->getBody()->getContents($body);
            $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
            return $this->response = $this->createResponse($jsonToArrayResponse, $httpResponse->getStatusCode());
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }

        return $this->response = new Response($this, $data);
    }

    protected function getCustomer(){

        $customer_info = array();
        if($this->getCard()->getExpiryMonth()<10){
            $exp_month = '0'.(String)$this->getCard()->getExpiryMonth();
        }else{
            $exp_month = (String)$this->getCard()->getExpiryMonth();
        }
        $token =array(
            "card[number]" => $this->getCard()->getNumber(),
            "card[exp_year]" =>  (String)$this->getCard()->getExpiryYear(),
            "card[exp_month]" => $exp_month,
            "card[cvc]" => $this->getCard()->getCvv()
        );
         $token_id=$this->MakeRequest($token,'v1/tokens');
         $customer_info['token_id']=$token_id["id"];
         $customer = array(
                "token_card" => $token_id["id"],
                'name' => $this->getCard()->getFirstName(),
                'last_name' => $this->getCard()->getLastName(),
                'address' => $this->getCard()->getAddress1(),
                //'line2' => $this->getCard()->getAddress2(),
                'city' => $this->getCard()->getCity(),
                //'state' => $this->getCard()->getState(),
                //'postal_code' => $this->getCard()->getPostcode(),
                //'country_code' => strtoupper($this->getCard()->getCountry()),
                "default" => true,
                'email' => $this->getCard()->getEmail()
        );
        $customer_id=$this->MakeRequest($customer,'payment/v1/customer/create');
        $customer_info['customer_id']=$customer_id["customerId"];
        return $customer_info;
    }

    protected function MakeRequest($data,$endpoin){
                    
        try {
            $body = $data ? json_encode($data): null;
            $headers = array("Content-Type" => "application/json", "Accept" => "application/json", "Type" => 'sdk-jwt', "lang" => "PHP",  'Authorization' => 'Bearer ' . $this->getToken());
            $httpResponse = $this->httpClient->request(
                'POST',
                $this->getEndpoint($endpoin),
                $headers,
                $body
            );
            // Empty response body should be parsed also as and empty array
            
            $body = (string) $httpResponse->getBody()->getContents($body);
           
            $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
            if($jsonToArrayResponse["status"]){
                return $jsonToArrayResponse["data"];
            }else{
                return;
            }
            
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }
}

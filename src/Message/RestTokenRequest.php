<?php

namespace Omnipay\Payco\Message;
use Omnipay\Common\Exception\InvalidResponseException;

class RestTokenRequest extends AbstractRestRequest
{
    public function getData()
    {
        return array('grant_type' => 'client_credentials');
    }

    protected function getEndpoint($url, $switch=null)
    {
        return parent::getEndpoint($url,$switch);
        
    }

    public function sendData($data)
    {        

        $jsonToArrayResponse=$this->getTokenBearer($this->getApiKey(),$this->getPrivateKey());

        if($jsonToArrayResponse["status"]){
            $status_code = 200;
        }else{
            $status_code = 404;  
        }
        try {
        return $this->response = new RestResponse($this, $jsonToArrayResponse, $status_code );
        } catch (\Exception $e) {
            throw new InvalidResponseException(
                'Error communicating with payment gateway: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    protected function getTokenBearer($api_key, $private_key){
        $url_login = "/v1/auth/login";
        try {
        if(!isset($_COOKIE[$api_key])) {
            $headers = array("Content-Type" => "application/json", "Accept" => "application/json", "Type" => 'sdk-jwt', "lang" => "PHP");
            $data_ = array(
                'public_key' => $api_key,
                'private_key' => $private_key
            );
            $body = $data_ ? json_encode($data_): null;
            $httpResponse = $this->httpClient->request(
            $this->getHttpMethod(),
            $this->getEndpoint($url_login),
            $headers,
            $body
        );
            $body = (string) $httpResponse->getBody()->getContents($body);
            $jsonToArrayResponse = !empty($body) ? json_decode($body, true) : array();
            if($jsonToArrayResponse["status"]){
                $bearer_token=$jsonToArrayResponse["bearer_token"];
                $cookie_name = $api_key;
                $cookie_value = $bearer_token;
                setcookie($cookie_name, $cookie_value, time() + (60 * 14), "/"); 
            }
        }else{
            $bearer_token = $_COOKIE[$api_key];
          } 

          $data = [
            "status" => true,
            "message" => 'Autenticación generada con    éxito',
            "bearer_token" => $bearer_token
        ];
           return $data;
             
        } catch (\Exception $e) {
            $data = [
                "status" => false,
                "message" => $e->getMessage(),
                "data" => []
            ];
            $objectReturnError = (object)$data;
            return $objectReturnError;
        }
    }

}

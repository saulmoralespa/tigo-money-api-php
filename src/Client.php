<?php


namespace TigoMoney;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

class Client
{
    const API_BASE_TOKEN_URL = "https://prod.api.tigo.com/v1/";
    const SANDBOX_API_BASE_TOKEN_URL = "https://securesandbox.tigo.com/v1/";

    const API_BASE_PAYMENT_URL = "https://prod.api.tigo.com/v2/";
    const SANDBOX_API_BASE_PAYMENT_URL = "https://securesandbox.tigo.com/v2/";

    protected static $_sandbox = false;
    protected $clientId;
    protected $clientSecret;
    public $agentAccount;
    public $agentPin;
    public $agentName;

    public function __construct($clientId, $clientSecret, $agentAccount, $agentPin, $agentName)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->agentAccount = $agentAccount;
        $this->agentPin = $agentPin;
        $this->agentName = $agentName;
    }

    public function sandboxMode($status = false)
    {
        if ($status)
            self::$_sandbox = true;
    }

    public static function getBaseTokenURL()
    {
        if (self::$_sandbox)
            return self::SANDBOX_API_BASE_TOKEN_URL;
        return self::API_BASE_TOKEN_URL;
    }

    public static function getBasePaymentURL()
    {
        if (self::$_sandbox)
            return self::SANDBOX_API_BASE_PAYMENT_URL;
        return self::API_BASE_PAYMENT_URL;
    }

    public function client($payment = true)
    {
        return new GuzzleClient([
            'base_uri' => $payment ? self::getBasePaymentURL() : self::getBaseTokenURL()
        ]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getToken()
    {
        try{
            $response = $this->client(false)->post("oauth/mfs/payments/tokens",
                [
                "headers" => [
                    "Content-Type" => "application/x-www-form-urlencoded",
                    "Authorization" => ["Basic " . $this->encodeCredentials()]
                ],
                "json" => [
                    "grant_type" => "client_credentials"
                ]
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            $message = self::getErrorMessage($exception->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function authorization(array $params)
    {

        $params = array_merge(
            [
            'MasterMerchant' => [
                'account' => $this->agentAccount,
                'pin' => $this->agentPin,
                'id' => $this->agentName
            ]
        ], $params);

        try{
            $response = $this->client()->post("tigo/mfs/payments/authorizations",
                [
                "headers" => [
                    "Authorization" =>  "Bearer ". $this->getToken()->accessToken,
                    "Content-Type" => "application/json"
                ],
                "json" => $params
            ]);

            return self::responseJson($response);

        }catch (RequestException $exception){
            $message = self::getErrorMessage($exception->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @param $mfsTransactionId
     * @param $merchantTransactionId
     * @return mixed
     * @throws \Exception
     */
    public function reverse($mfsTransactionId, $merchantTransactionId)
    {
        try{
            $response = $this->client()->delete("tigo/mfs/payments/transactions/PRY/$mfsTransactionId/$merchantTransactionId",
                [
                "headers" => [
                    "Authorization" =>  "Bearer ". $this->getToken()->accessToken,
                    "Content-Type" => "application/json"
                ],
            ]);
            return self::responseJson($response);
        }catch (RequestException $exception){
            $message = self::getErrorMessage($exception->getMessage());
            throw new \Exception($message);
        }
    }

    /**
     * @param $mfsTransactionId
     * @param $merchantTransactionId
     * @return mixed
     * @throws \Exception
     */
    public function getTransaction($mfsTransactionId, $merchantTransactionId)
    {
        try{
            $response = $this->client()->get("tigo/mfs/payments/transactions/PRY/$mfsTransactionId/$merchantTransactionId",
                [
                "headers" => [
                    "Authorization" =>  "Bearer ". $this->getToken()->accessToken,
                    "Content-Type" => "application/json"
                ],
            ]);
            return self::responseJson($response);
        }catch (RequestException $exception){
            $message = self::getErrorMessage($exception->getMessage());
            throw new \Exception($message);
        }
    }

    public function encodeCredentials()
    {
        $access = $this->clientId . ":" . $this->clientSecret;
        return base64_encode($access);
    }

    public static function responseJson($response)
    {
        return \GuzzleHttp\json_decode(
            $response->getBody()->getContents()
        );
    }

    public static function getErrorMessage($response)
    {
        $pattern = "~{(.*?)}~";

        preg_match($pattern, $response, $matches);

        if (empty($matches))
            return $response;

        $json = \GuzzleHttp\json_decode($matches[0]);

        return $json->error_description;
    }
}
<?php

use TigoMoney\Client;
use PHPUnit\Framework\TestCase;

class TigoMoneyTest extends TestCase
{
    public $tigomoney;

    public function setUp()
    {
        $dotenv = Dotenv\Dotenv::createMutable(__DIR__ . '/../');
        $dotenv->load();

        $clientId = getenv('API_KEY'); //Unique client identifier assigned during the registration process with Tigo Money
        $clientSecret = getenv('API_SECRET'); //Secret password provided during the registration process with Tigo Money
        $agentAccount = getenv('AGENT_ACCOUNT');
        $agentPin = getenv('AGENT_PIN');
        $agentName = getenv('AGENT_NAME');

        $this->tigomoney = new Client($clientId, $clientSecret, $agentAccount, $agentPin, $agentName);
        $this->tigomoney->sandboxMode(false);
    }

    public function testAuth()
    {
        $response = $this->tigomoney->getToken();
        var_dump($response);
        $this->assertObjectHasAttribute("accessToken", $response);
    }

    public function testAuthorization()
    {
        $merchantTransactionId = (string)time();

        $params = array (
            'Subscriber' =>
                array (
                    'account' => '0986777961',
                    'countryCode' => '595',
                    'country' => 'PRY',
                    'emailId' => 'johndoe@mail.com',
                ),
            'redirectUri' => 'https://www.4gamersclub.com/',
            'callbackUri' => 'https://www.4gamersclub.com/',
            'language' => 'spa',
            'OriginPayment' =>
                array (
                    'amount' => '12',
                    'currencyCode' => 'PYG',
                    'tax' => '0.00',
                    'fee' => '0.00',
                ),
            'exchangeRate' => '1',
            'LocalPayment' =>
                array (
                    'amount' => '12',
                    'currencyCode' => 'PYG',
                ),
            'merchantTransactionId' => $merchantTransactionId,
        );

        $response = $this->tigomoney->authorization($params);
        var_dump($response);

    }

    public function testReverse()
    {
        $merchantTransactionId = "1579451060"; //merchant transaction number
        $mfsTransactionId = "shopName"; // Shop name
        $response = $this->tigomoney->reverse($mfsTransactionId, $merchantTransactionId);
        var_dump($response);
    }

    public function testGetTransaction()
    {
        $merchantTransactionId = "1579451060"; //merchant transaction number
        $mfsTransactionId = "shopName"; // Shop name
        $response = $this->tigomoney->getTransaction($mfsTransactionId, $merchantTransactionId);
        var_dump($response);
    }
}
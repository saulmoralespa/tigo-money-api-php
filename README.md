Tigo Money API PHP
============================================================

## Installation

Use composer package manager

```bash
composer require saulmoralespa/tigo-money-api-php
```

```php
// ... please, add composer autoloader first
include_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// import client class
use TigoMoney\Client;

$clientId = getenv('API_KEY'); //Unique client identifier assigned during the registration process with Tigo Money
$clientSecret = getenv('API_SECRET'); //Secret password provided during the registration process with Tigo Money
$agentAccount = getenv('AGENT_ACCOUNT');
$agentPin = getenv('AGENT_PIN');
$agentName = getenv('AGENT_NAME');

$tigoMoney = new Client($clientId, $clientSecret, $agentAccount, $agentPin, $agentName);
$tigoMoney->sandboxMode(true); //true for tests, false for production
```
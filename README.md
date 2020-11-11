# payumoney-payment-gateway-php
A simple PayUMoney payment gateway integration library

# PayUMoney API for PHP
create a merchant account on [PayUMoney](https://www.payumoney.com/) site

Simple library for accepting payments via [PayUMoney](https://www.payumoney.com/).

## Installation
The recommended way to install this library [Composer](http://getcomposer.org):

```sh
composer require ajaz/payumoney-payment-gateway-php

```

## Usage

You'll find a minimal usage example below.

### Initialize purchase

```php
<?php
// purchase.php

use PaymentGateway\PayUmoney\PayUMoney;

require 'vendor/autoload.php';

$payumoney = new PayUMoney(array(
    'merchantId' => 'YOUR_MERCHANT_ID',
    'secretKey'  => 'YOUR_SECRET_KEY',
    'testMode'   => true
));

// All of these parameters are required!
$params = [
    'txnid'       => 'TXN65876798779',
    'amount'      => 100.00,
    'productinfo' => 'Buy a Sunglasses',
    'firstname'   => 'Garry',
    'email'       => 'jack@example.com',
    'phone'       => '1234567890',
    'surl'        => 'http://localhost/payumoney-payment-gateway-php/return.php',
    'furl'        => 'http://localhost/payumoney-payment-gateway-php/return.php',
    'udf1'        =>  'USER ID' //optional
];

// Redirects to PayUMoney
$data = $payumoney->initializePurchase($params)->send();

$output = sprintf('<form id="payment_form" method="POST" action="%s">', $payumoney->getServiceUrl());

        foreach ($data as $key => $value) {
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', $key, $value);
        }

        $output .= '<input type="hidden" name="service_provider" value="payu_paisa" size="64" />';

        $output .= '<div id="redirect_info" style="display: none">Redirecting...</div>
                <input id="payment_form_submit" type="submit" value="Proceed to PayUMoney" />
            </form>
            <script>
                document.getElementById(\'redirect_info\').style.display = \'block\';
                document.getElementById(\'payment_form_submit\').style.display = \'none\';
                document.getElementById(\'payment_form\').submit();
            </script>';
 echo $output;
```

### Finalize purchase

```php
<?php
// return.php

use PaymentGateway\PayUmoney\PayUMoney;
use PaymentGateway\PayUmoney\PurchaseResult;

require 'vendor/autoload.php';

$payumoney = new PayUMoney([
    'merchantId' => 'YOUR_MERCHANT_ID',
    'secretKey'  => 'YOUR_SECRET_KEY',
    'testMode'   => true
]);

$result = $payumoney->completePurchase($_POST);

if ($result->checksumIsValid() && $result->getStatus() === PurchaseResult::STATUS_COMPLETED) {
  print 'Payment was successful.';
} else {
  print 'Payment was not successful.';
}
```

The `PurchaseResult` has a few more methods that might be useful:

```php
$result = $payumoney->completePurchase($_POST);

// Returns Complete, Pending, Failed or Tampered
$result->getStatus(); 

// Returns an array of all the parameters of the transaction
$result->getParams();

// Returns the ID of the transaction
$result->getTransactionId();

// Returns true if the checksum is correct
$result->checksumIsValid();
```

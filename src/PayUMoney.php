<?php

namespace PaymentGateway\PayUmoney;

/**
 * Payumoney payment gateway librray to handle the payment gateway
 * @author Ajaz Alam <ajazsaim@gmail.com>
 */


class PayUMoney
{

	/**
     * SANDBOX URL for testing purpose
     */
	const TEST_URL = 'https://sandboxsecure.payu.in/_payment';

	/**
     * PRODUCTION URL
     */
	const PRODUCTION_URL = 'https://secure.payu.in/_payment';

	/**
     * @var string
     */

	private $merchantId;

	/**
     * @var string
     */

	private $secretKey;

	/**
     * @var bool
     */

	private $testMode = true;

	/**
     * @param array $options
     */

	public function __construct(array $options)
	{
		if(!isset($options['merchantId']))
		{
			throw new \InvalidArgumentException('Please specify the "merchantId" key in $options array');
		}

		if(!isset($options['secretKey']))
		{
			throw new \InvalidArgumentException('Please specify the "secretKey" key in $options array');
		}

		if(isset($options['testMode']))
		{
			$this->testMode = $options['testMode'];
		}

		$this->merchantId = $options['merchantId'];
		$this->secretKey = $options['secretKey'];
	}

	/**
     * Get $merchantId property
     * @return string
     */

	public function getMerchantId()
	{
		return $this->merchantId;
	}

	 /**
     * Get $secretKey property
     * @return string
     */

     public function getSecretKey()
     {
     	return $this->secretKey;
     }

     /**
     * Get sandbox or production envirement
     * @return bool
     */

     public function getTestMode()
     {
     	return $this->testMode;
     }

     /**
     * @return string
     */

     public function getServiceUrl()
     {
     	return $this->testMode ? self::TEST_URL : self::PRODUCTION_URL;
     }

     /**
     * @return array
     */

     public function getChecksumParams()
     {
     	return array_merge(
     		['txnid','amount','productinfo','firstname','email'],
     		array_map(function ($i) {
     			return "udf{$i}";
     		}, range(1, 10))
     	);
     }

     /**
     * @param array $params
     *
     * @return string
     */

     private function getChecksum(array $params)
     {
     	$values = array_map(function ($field) use ($params) {
     			return isset($params[$field]) ? $params[$field] : '';
     		},
     		$this->getChecksumParams()
     	);

     	$values = array_merge([$this->getMerchantId()], $values, [$this->getSecretKey()]);

          return hash('sha512', implode('|', $values));
     }

     /**
     * @param array $params
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */

     public function initializePurchase(array $params)
     {
          $requiredParams = ['txnid','amount','firstname','email','phone','productinfo','surl','furl'];

          foreach ($requiredParams as $requiredParam)
          {
               if(!isset($params[$requiredParam]))
               {
                    throw new \InvalidArgumentException(sprintf('"%s" is a required param.', $requiredParam));
                    
               }
          }

          //get $params
          $params = array_merge($params, [
               'hash' => $this->getChecksum($params), 
               'key' => $this->getMerchantId()
          ]);

          $params = array_map(function ($param) {
               return htmlentities($param, ENT_QUOTES, 'utf-8', false);
          }, $params);

          return $params;
     }

     /**
     * @param array $params
     *
     * @return PurchaseResult
     */

     public function completePurchase(array $params)
     {
          return new PurchaseResult($this, $params);
     }
}
<?php


namespace Equity;

class Equitel
{

	private $key;
	private $secret;

	public function __construct( $app_key, $app_secret, $id )
	{
		$this -> key = $app_key;
		$this -> secret = $app_secret;
		$this -> id = $id;
	}

	public function changePassword()
	{
		$url = "https://api.equitybankgroup.com/identity/v1-sandbox/merchants/".$this -> id ."/changePassword";
	}

	public function accessToken()
	{
		$url = "https://api.equitybankgroup.com/identity/v1-sandbox/token";
	}

	public function purchaseAirtime( $value = '' )
	{
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/airtime";
	}

	//Allows a Remittance Agent to transfer money to a Recipient Account in real-time.
	public function onlineRemittance(){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/remittance";
	}

	//A merchant can view the latest status of a transaction being processed
	public function paymentStatus( $transactionId ){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments/".$transactionId;
	}

	public function createPayment(){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments";
	}
}
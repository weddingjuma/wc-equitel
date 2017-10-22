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

	public function purchaseAirtime( $mobileNumber, $amount, $reference, $telco = "equitel" )
	{
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/airtime";
		$data = array (
		    "customer" => array (
		        "mobileNumber" => $mobileNumber
		    ),
		    "airtime" => array (
		        "amount" => $amount,
		        "reference" => $reference,
		        "telco" => $telco
		    )
		);

		$result = $ajax -> post( $url, json_encode( $data ) );

		return json_decode( $result );
	}

	//Allows a Remittance Agent to transfer money to a Recipient Account in real-time.
	public function remit(){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/remittance";

		$data = array (
		    "transactionReference" => "",
		    "source" => array (
		        "senderName" => ""
		    ),
		    "destination" => array (
		        "accountNumber" => "",
		        "bicCode" => "",
		        "mobileNumber" => "",
		        "walletName" => "",
		        "bankCode" => "",
		        "branchCode" => ""
		    ),
		    "transfer" => array (
		        "countryCode" => "",
		        "currencyCode" => "",
		        "amount" => "",
		        "paymentType" => "",
		        "paymentReferences" => [ "" ],
		        "remarks" => ""
		    )
		);
	}

	//A merchant can view the latest status of a transaction being processed
	public function status( $transactionId ){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments/".$transactionId;

		$data = $ajax -> get( $url );

		return $data;
	}

	public function pay(){
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments";
		$data = array (
		    "customer" => array (
		        "mobileNumber" => ""
		    ),
		    "transaction" => array (
		        "amount" => "",
		        "description" => "",
		        "type" => "",
		        "auditNumber" => ""
		    )
		);
	}
}
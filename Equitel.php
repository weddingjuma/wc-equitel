<?php


namespace Equity;

class Equitel
{

	private $key;
	private $secret;
	private $id;
	protected $live;

	public function __construct( $app_key, $app_secret, $id, $live = true )
	{
		$this -> key = $app_key;
		$this -> secret = $app_secret;
		$this -> id = $id;
		$this -> live = $live;
	}

	public function changePassword()
	{
		$url = "https://api.equitybankgroup.com/identity/v1-sandbox/merchants/".$this -> id ."/changePassword";
	}

	public function accessToken()
	{
		$url = "https://api.equitybankgroup.com/identity/v1-sandbox/token";
	}

	public function airtime( $mobileNumber, $amount, $reference, $telco = "equitel" )
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
	public function remit( $transactionReference, $senderName, $accountNumber, $bicCode, $mobileNumber, $walletName, $bankCode, $branchCode, $amount, $countryCode = "254", $currencyCode = "KSH", $paymentType = "", $remarks = "Remittance" )
	{
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/remittance";

		$data = array (
		    "transactionReference" => $transactionReference,
		    "source" => array (
		        "senderName" => $senderName
		    ),
		    "destination" => array (
		        "accountNumber" => $accountNumber,
		        "bicCode" => $bicCode,
		        "mobileNumber" => $mobileNumber,
		        "walletName" => $walletName,
		        "bankCode" => $bankCode,
		        "branchCode" => $branchCode
		    ),
		    "transfer" => array (
		        "countryCode" => $countryCode,
		        "currencyCode" => $currencyCode,
		        "amount" => $amount,
		        "paymentType" => $paymentType,
		        "paymentReferences" => [ "" ],
		        "remarks" => $remarks
		    )
		);
	}

	//A merchant can view the latest status of a transaction being processed
	public function status( $transactionId )
	{
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments/".$transactionId;

		$data = $ajax -> get( $url );

		return $data;
	}

	public function pay( $mobileNumber, $amount, $description, $type, $auditNumber )
	{
		$url = "https://api.equitybankgroup.com/transaction/v1-sandbox/payments";
		$data = array (
		    "customer" => array (
		        "mobileNumber" => $mobileNumber
		    ),
		    "transaction" => array (
		        "amount" => $amount,
		        "description" => $description,
		        "type" => $type,
		        "auditNumber" => $auditNumber
		    )
		);
	}
}
<?php
$Amount 	= 500;
$MerchantID = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";

if (isset($_POST['ResNum']) && $_POST['ResNum'] != "")
{
	$params = array(
		'State' 	=> (isset($_POST['State'])) 	? $_POST['State'] 		: "",
		'StateCode' => (isset($_POST['StateCode'])) ? $_POST['StateCode'] 	: "",
		'ResNum' 	=> (isset($_POST['ResNum'])) 	? $_POST['ResNum'] 		: "",
		'MID' 		=> (isset($_POST['MID'])) 		? $_POST['MID'] 		: "",
		'RefNum' 	=> (isset($_POST['RefNum'])) 	? $_POST['RefNum'] 		: "",
		'CID' 		=> (isset($_POST['CID'])) 		? $_POST['CID'] 		: "",
		'TRACENO' 	=> (isset($_POST['TRACENO'])) 	? $_POST['TRACENO'] 	: "",
		'RRN' 		=> (isset($_POST['RRN'])) 		? $_POST['RRN'] 		: "",
		'Amount' 	=> (isset($_POST['Amount'])) 	? $_POST['Amount'] 		: "",
		'website' 	=> (isset($_POST['website'])) 	? $_POST['website'] 	: "",
		'SecurePan' => (isset($_POST['SecurePan'])) ? $_POST['SecurePan'] 	: ""
	);

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://www.zarinpal.com/pg/transaction/verify/{$_POST['ResNum']}");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$curl_exec = curl_exec($curl);
	curl_close($curl);

	preg_match('/Authority=(.*?)"/', $curl_exec, $zarinpal_data);

	$zarinpal_data 		= $zarinpal_data[1];
	$zarinpal_data 		= urldecode($zarinpal_data);
	$zarinpal_data 		= str_replace("&", "", $zarinpal_data);
	$zarinpal_data 		= str_replace("amp;", "", $zarinpal_data);
	$zarinpal_data 		= str_replace("Status=", "|", $zarinpal_data);
	$zarinpal_data 		= explode("|", $zarinpal_data);
	$zarinpal_au 		= (isset($zarinpal_data[0]) && $zarinpal_data[0] != "") ? $zarinpal_data[0] : "";
	$zarinpal_au 		= preg_replace("/[^0-9]/", "", $zarinpal_au);
	$zarinpal_status 	= (isset($zarinpal_data[1]) && $zarinpal_data[1] != "") ? $zarinpal_data[1] : "";
} else {
	$zarinpal_au 		= (isset($_GET['Authority']) && $_GET['Authority'] != "") ? $_GET['Authority'] : "";
	$zarinpal_status 	= (isset($_GET['Status']) && $_GET['Status'] != "") ? $_GET['Status'] : "";
}

if (isset($zarinpal_au) && $zarinpal_au != "" && ($zarinpal_au * 1) > 0 && isset($zarinpal_status) && ($zarinpal_status == "NOK" || $zarinpal_status == "OK"))
{
	if ($zarinpal_status == "OK")
	{
		$client = new SoapClient("https://www.zarinpal.com/pg/services/WebGate/wsdl");
		$result = $client->PaymentVerificationWithExtra(array(
			'MerchantID'     => $MerchantID,
			'Authority'      => $zarinpal_au,
			'Amount'         => $Amount,
		));

		$extraDetail = (isset($result->ExtraDetail) 	&& $result->ExtraDetail != "") 	? json_decode($result->ExtraDetail) : "";
		$Transaction = (isset($extraDetail->Transaction) && $extraDetail->Transaction != "") ? $extraDetail->Transaction 	: "";
		$CardPanMask = (isset($Transaction->CardPanMask) && $Transaction->CardPanMask != "") ? $Transaction->CardPanMask 	: "";

		if (isset($result->Status) && $result->Status == 100)
		{
			// Successful transactions
			echo "Transaction Success. RefID : {$result->RefID} - Cart Number : {$CardPanMask}";
		} else {
			// Transaction failed
			echo "Error : {$result->Status}";
		}
	} else {
		// Transaction canceled
		echo 'Transaction canceled by user';
	}
} else {
	if (isset($curl_exec) && $curl_exec != "")
	{
		echo $curl_exec;
	} else {
		// Webservice error
		echo 'Webservice Error';
	}
}
?>

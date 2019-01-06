<?php 
$Amount 			= 500;
$MerchantID 		= "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
$Description 		= "Paymend by miladworkshop.ir";
$Email 				= "";
$Mobile 			= "";
$CallbackURL 		= "http://example.com/verify.php";

$client = new SoapClient("https://www.zarinpal.com/pg/services/WebGate/wsdl");
$result = $client->PaymentRequestWithExtra(array(
	'MerchantID'     => $MerchantID,
	'Amount'         => $Amount,
	'Description'    => $Description,
	'AdditionalData' => "",
	'Email'          => $Email,
	'Mobile'         => $Mobile,
	'CallbackURL'    => $CallbackURL,
));

if ($result->Status == 100)
{
	$redirect_form = @file_get_contents("https://www.zarinpal.com/pg/StartPay/{$result->Authority}/Sep");

	if (isset($redirect_form) && $redirect_form != "" && strpos($redirect_form, "shaparak.ir") !== false)
	{
		preg_match('/name="Token" value="(.*?)"/', $redirect_form, $sep_token);		

		if (isset($sep_token[1]) && $sep_token[1] != "")
		{
			echo "<form target='_parent' id='Bank' action='https://sep.shaparak.ir/Payment.aspx' method='POST' style='display: none'><input type='hidden' name='Token' value='{$sep_token[1]}' /><input type='hidden' name='RedirectURL' value='{$CallbackURL}' /></form><script>setTimeout(function(){ document.getElementById('Bank').submit();}, 500);</script>";
		} else {
			echo $redirect_form;
		}
	} else {
		header("Location: https://www.zarinpal.com/pg/StartPay/{$result->Authority}/ZarinGate");
	}
} else {
	echo "Error {$result->Status}";
}
?>

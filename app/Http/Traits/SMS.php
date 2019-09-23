<?php
namespace App\Http\Traits;
trait SMS {

	public function sendSMS($number, $text) {
		$curl   = curl_init();
		$string = "AccountId=101007068&Password=Vodafone.1&SenderName=CrepeWaffle&ReceiverMSISDN=$number&SMSText=$text";
		$secret = "F0113A02AEC04B459498CB4DF007C796";
		$hash   = strtoupper(hash_hmac('sha256', $string, $secret));
		curl_setopt_array($curl, array(
			CURLOPT_URL            => "https://e3len.vodafone.com.eg/web2sms/sms/submit/",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => '<SubmitSMSRequest xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:="http://www.edafa.com/web2sms/sms/model/" xsi:schemaLocation="http://www.edafa.com/web2sms/sms/model/SMSAPI.xsd " xsi:type="SubmitSMSRequest">
									    <AccountId>101007068</AccountId>
									    <Password>Vodafone.1</Password>
									    <SecureHash>' . $hash . '</SecureHash>
									    <SMSList>
									        <SenderName>CrepeWaffle</SenderName>
									        <ReceiverMSISDN>'.$number.'</ReceiverMSISDN>
									        <SMSText>'.$text.'</SMSText>
									    </SMSList>
									</SubmitSMSRequest>',
			CURLOPT_HTTPHEADER     => array(
				"Accept-Encoding: gzip,deflate",
				"Connection: Keep-Alive",
				"Content-Type: application/xml",
				"Host: e3len.vodafone.com.eg",
			),
		));

		$response = curl_exec($curl);
		$err      = curl_error($curl);

		curl_close($curl);

	}
}

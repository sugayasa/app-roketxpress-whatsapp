<?php
namespace App\Libraries;

class AIBot
{
    public function changeHandleStatus($status, $clientPhone)
    {
        $response	=	"";
	    $httpCode	=	500;
        $arrStatus  =   ['ai', 'human'];
        $status     =   $arrStatus[$status] ?? 'human';

		try {
            $timeStamp          =   time();
            $arrPostData        =   [
                'status'        =>  $status,
                'client_phone'  =>  $clientPhone,
                'timestamp'     =>  $timeStamp
            ];

            $curl       =   $this->executeAIBOTEndpoint($timeStamp, $arrPostData, AIBOT_CHANGE_HANDLE_STATUS_ENDPOINT);
			$response	=	curl_exec($curl);
			$httpCode	=	curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
		} catch (\Exception $e) {
            log_message('error', 'AIBot changeHandleStatus error: '.$e->getMessage());
		}

        log_message('debug', 'AIBot changeHandleStatus completed successfully'.json_encode(['httpCode' => $httpCode, 'response' => $response]));
		return [
			'httpCode'	=>	$httpCode,
			'response'	=>	json_encode($response)
		];
    }

    public function sendTemplateMessageToBOT($clientPhone, $message, $detailReservationsData)
    {
        $response	=	"";
	    $httpCode	=	500;

		try {
            $timeStamp          =   time();
            $arrPostData        =   [
                'text'              =>  json_encode($detailReservationsData),
                'client_phone'      =>  $clientPhone,
                'initiate_booking'  =>  'true'
            ];

            $curl       =   $this->executeAIBOTEndpoint($timeStamp, $arrPostData, AIBOT_SEND_TEMPLATE_MESSAGE_ENDPOINT, 2);
			$response	=	curl_exec($curl);
			$httpCode	=	curl_getinfo($curl, CURLINFO_HTTP_CODE);

			curl_close($curl);
		} catch (\Exception $e) {
            log_message('error', 'AIBot send template message to BOT error: '.$e->getMessage());
		}

        log_message('debug', 'AIBot sendTemplateMessageToBOT completed successfully'.json_encode(['httpCode' => $httpCode, 'response' => $response]));
		return [
			'httpCode'	=>	$httpCode,
			'response'	=>	json_encode($response)
		];
    }

    private function executeAIBOTEndpoint($timeStamp, $arrPostData, $endPoint, $timeout = 0) {
        $curl               =	curl_init();
        $arrDataSignature   =   [
            'timestamp'     =>  $timeStamp
        ];
        $hmacSignature  =   $this->getSignatureAIBot($arrDataSignature);

        curl_setopt_array($curl, array(
            CURLOPT_URL				=>	AIBOT_BASE_URL.$endPoint,
            CURLOPT_RETURNTRANSFER	=>	true,
            CURLOPT_ENCODING		=>	'',
            CURLOPT_MAXREDIRS		=>	10,
            CURLOPT_TIMEOUT			=>	$timeout,
            CURLOPT_FOLLOWLOCATION	=>	true,
            CURLOPT_HTTP_VERSION	=>	CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST	=>	'POST',
            CURLOPT_POSTFIELDS      =>  http_build_query($arrPostData),
            CURLOPT_HTTPHEADER  =>	array(
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
                'BST-Public-Key: '.AIBOT_PUBLIC_KEY,
                'BST-Signature: '.$hmacSignature,
                'BST-Timestamp: '.$timeStamp
            )
        ));

        return $curl;
    }

    private function getSignatureAIBot($arrDataSignature){
        $dataJSON       =   json_encode($arrDataSignature);
        $privateKey     =   AIBOT_PRIVATE_KEY;
        $hmacSignature  =   hash_hmac('sha256', $dataJSON, $privateKey);

        return $hmacSignature;
    }
}
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
			$curl	        =	curl_init();
            $timeStamp      =   time();
            $hmacSignature  =   $this->getSignatureAIBot($status, $clientPhone, $timeStamp);

            curl_setopt_array($curl, array(
                CURLOPT_URL				=>	AIBOT_CHANGE_HANDLE_STATUS_URL,
                CURLOPT_RETURNTRANSFER	=>	true,
                CURLOPT_ENCODING		=>	'',
                CURLOPT_MAXREDIRS		=>	10,
                CURLOPT_TIMEOUT			=>	0,
                CURLOPT_FOLLOWLOCATION	=>	true,
                CURLOPT_HTTP_VERSION	=>	CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST	=>	'POST',
                CURLOPT_POSTFIELDS      =>  http_build_query([
                    'status'        =>  $status,
                    'client_phone'  =>  $clientPhone,
                    'timestamp'     =>  $timeStamp
                ]),
                CURLOPT_HTTPHEADER  =>	array(
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    'BST-Public-Key: '.AIBOT_PUBLIC_KEY,
                    'BST-Signature: '.$hmacSignature,
                    'BST-Timestamp: '.$timeStamp
                )
			));

			$response	=	curl_exec($curl);
			$httpCode	=	curl_getinfo($curl, CURLINFO_HTTP_CODE);

			curl_close($curl);
		} catch (\Exception $e) {
            log_message('error', 'AIBot changeHandleStatus error: '.$e->getMessage());
		}

        log_message('debug', 'Response: '.json_encode($response));
		return [
			'httpCode'	=>	$httpCode,
			'response'	=>	json_encode($response)
		];
    }

    private function getSignatureAIBot($status, $clientPhone, $timeStamp){
        $dataJSON       =   json_encode(['status'=>$status, 'client_phone'=>$clientPhone, 'timestamp'=>$timeStamp]);
        $privateKey     =   AIBOT_PRIVATE_KEY;
        $hmacSignature  =   hash_hmac('sha256', $dataJSON, $privateKey);

        return $hmacSignature;
    }
}
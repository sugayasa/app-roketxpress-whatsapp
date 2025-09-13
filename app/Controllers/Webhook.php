<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Libraries\FirebaseRTDB;
use App\Libraries\WhatsappMessage;
use App\Models\CronModel;
use App\Models\MainOperation;
use CodeIgniter\I18n\Time;

class Webhook extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $epochDatetime;
    public function __construct()
    {
        $this->epochDatetime    =   Time::now('UTC')->getTimestamp();
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function whatsappOneMsgIO()
    {
        $mainOperation  =   new MainOperation();
        $cronModel      =   new CronModel();
        $firebaseRTDB   =   new FirebaseRTDB();
        $whatsappMessage=   new WhatsappMessage();
        $dateTimeNow    =   date('Y-m-d H:i:s');
        $params         =   $this->request->getJSON();
        $messages       =   $params->messages ?? null;
        $acks           =   $params->ack ?? null;

        if(!is_null($messages)) {
            foreach ($messages as $message) {
                $whatsappMessage->saveWebhookMessage($message, $dateTimeNow);
            }
        } else if(!is_null($acks)) {
            foreach ($acks as $ack) {
                $idMessage      =   $ack->id ?? null;
                $ackStatus      =   $ack->status ?? null;
                $fieldDateTime  =   "DATETIMESENT";
                $detailChatCron =   $cronModel->getDetailChatCron($idMessage);
                $idReservationRC=   $detailChatCron['IDRESERVATIONRECONFIRMATION'];
                $dateTimeNow    =   Time::now()->toDateTimeString();

                switch ($ackStatus) {
                    case 'sent'     :   $fieldDateTime    =   'DATETIMESENT'; break;
                    case 'delivered':   $fieldDateTime    =   'DATETIMEDELIVERED'; break;
                    case 'read'     :   $fieldDateTime    =   'DATETIMEREAD'; break;
                    default         :   $fieldDateTime    =   'DATETIMESENT'; break;
                }

                if($idReservationRC != 0){
                    $arrUpdateReconfirmation    =   false;
                    switch ($ackStatus) {
                        case 'sent'     :   $arrUpdateReconfirmation    =   [
                                                'DATETIMESENT'  =>  $dateTimeNow,
                                                'STATUS'        =>  1
                                            ];
                                            break;
                        case 'read'     :    $arrUpdateReconfirmation    =   [
                                                'DATETIMEREAD'  =>  $dateTimeNow,
                                                'STATUSREAD'    =>  1
                                            ];
                                            break;
                        case 'delivered':   
                        default         :   break;
                    }
                    if($arrUpdateReconfirmation != false) $mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationreconfirmation', $arrUpdateReconfirmation, ['IDRESERVATIONRECONFIRMATION' => $idReservationRC]);
                }

                $arrUpdateChatthread    =   [$fieldDateTime  =>  $this->epochDatetime];
                $mainOperation->updateDataTable('t_chatthread', $arrUpdateChatthread, ['IDMESSAGE' => $idMessage]);
                $arrUpdateReferenceRTDB =   [
                    'idMessage' =>  $idMessage,
                    'timestamp' =>  $this->epochDatetime,
                    'type'      =>  $ackStatus
                ];
                $firebaseRTDB->updateRealtimeDatabaseValue('currentACK', $arrUpdateReferenceRTDB);
            }
        }

        if(LOG_WEBHOOK_MESSAGE) $this->insertWebhookLog($params);
        return throwResponseOK('Data saved successfully');
    }

    private function insertWebhookLog($params)
    {
        $mainOperation  =   new MainOperation();
        $arrInsert      =   [
            'PARAMETERDATA' =>  json_encode($params),
            'LOGDATETIME'   =>  date('Y-m-d H:i:s')
        ];
        $mainOperation->insertDataTable('log_webhook', $arrInsert);

        return true;
    }

    public function handleHuman()
    {
        $whatsappMessage=   new WhatsappMessage();
        $signatureHeader=   $this->request->header('BST-Signature');

        if (!isset($signatureHeader) || is_null($signatureHeader) || $signatureHeader === '') return $this->failUnauthorized('Signature header is missing');

        $signatureHeader=   $signatureHeader->getValueLine();
        $params         =   $this->request->getJSON(true);
        $timeStamp      =   $this->epochDatetime;
        $timeStampMin   =   $timeStamp - 120;
        $timeStampMax   =   $timeStamp + 120;
        $isValidRequest =   false;
        $privateKey     =   ECOMMERCE_PRIVATE_KEY;
        
        for($timeStampCheck = $timeStampMin; $timeStampCheck <= $timeStampMax; $timeStampCheck++) {
            $dataRequest    =   array_merge($params, ['timestamp' => $timeStampCheck]);
            $dataJSON       =   json_encode($dataRequest);
            $hmacSignature  =   hash_hmac('sha256', $dataJSON, $privateKey);

            if ($hmacSignature === $signatureHeader) {
                $isValidRequest = true;
                break;
            }
        }

        if (!$isValidRequest) return throwResponseForbidden('Invalid signature');

        $phoneNumber        =   $params['phoneNumber'] ?? null;

        if (is_null($phoneNumber) || $phoneNumber === '') return throwResponseNotAcceptable('Phone number is required');

        $mainOperation      =   new MainOperation();
        $phoneNumber        =	preg_replace('/[^0-9]/', '', $phoneNumber);
        $phoneNumberBase    =   $whatsappMessage->getDataPhoneNumberBase($phoneNumber);
        $idCountry          =   $phoneNumberBase['idCountry'] ?? 0;
        $phoneNumberBase    =   $phoneNumberBase['phoneNumberBase'] ?? $phoneNumber;
        $detailChatList     =   $mainOperation->getDetailChatListByPhoneNumber($idCountry, $phoneNumberBase);
        $idChatList         =   $detailChatList['IDCHATLIST'] ?? null;

        if (is_null($idChatList) || $idChatList === '') return throwResponseNotAcceptable('Unkown contact, invalid phone number');
        $arrUpdateChatList  =   [
            'HANDLEFORCE'   =>  1,
            'HANDLESTATUS'  =>  2
        ];
        $procUpdate         =   $mainOperation->updateDataTable('t_chatlist', $arrUpdateChatList, ['IDCHATLIST' => $idChatList]);
        
        if(!$procUpdate['status']) {
            if($procUpdate['errCode'] === 1329) {
                return throwResponseOK('Force handle already exists');
            } else {
                return switchMySQLErrorCode($procUpdate['errCode']);
            }
        }

        $mainOperation->updateChatListAndRTDBStats($idChatList, false);
        return throwResponseOK('Force handle updated successfully');
    }

    public function getSignature()
    {
        $dataRequest    =   $this->request->getJSON(true);

        if(!isset($dataRequest['timestamp']) || is_null($dataRequest['timestamp']) || $dataRequest['timestamp'] === '') {
            return throwResponseNotAcceptable('Timestamp is required');
        }

        if(!isset($dataRequest['phoneNumber']) || is_null($dataRequest['phoneNumber']) || $dataRequest['phoneNumber'] === '') {
            return throwResponseNotAcceptable('Phone number is required');
        }

        $dataJSON       =   json_encode($dataRequest);
        $privateKey     =   ECOMMERCE_PRIVATE_KEY;
        $hmacSignature  =   hash_hmac('sha256', $dataJSON, $privateKey);

        return throwResponseOK('Signature retrieved successfully', ['signature' => $hmacSignature, 'dataRequest' => $dataRequest]);
    }
}
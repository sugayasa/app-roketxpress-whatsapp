<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Libraries\FirebaseRTDB;
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
        $dateTimeNow    =   date('Y-m-d H:i:s');
        $params         =   $this->request->getJSON();
        $messages       =   $params->messages ?? null;
        $acks           =   $params->ack ?? null;

        if(!is_null($messages)) {
            foreach ($messages as $message) {
                $author             =   $message->author ?? null;
                $chatId             =   $message->chatId ?? null;
                $messageId          =   $message->id ?? null;
                $messageType        =   $message->type ?? null;
                $messageBody        =   $message->body ?? null;
                $senderName         =   $message->senderName ?? null;
                $fromMe             =   $message->fromMe ?? null;
                $caption            =   $message->caption ?? null;
                $quotedMsgId        =   $message->quotedMsgId ?? null;
                $isForwarded        =   $message->isForwarded ?? null;
                $timeStamp          =   $message->time ?? null;
                $phoneNumber        =   getPhoneNumberFromWhatsappAuthor($chatId);
                $phoneNumber        =	preg_replace('/[^0-9]/', '', $phoneNumber);
                $phoneNumberBase    =   $this->getDataPhoneNumberBase($phoneNumber);
                $idCountry          =   $phoneNumberBase['idCountry'] ?? 0;
                $phoneNumberBase    =   $phoneNumberBase['phoneNumberBase'] ?? $phoneNumber;
                $isZeroPrefixNumber =   $phoneNumberBase['isZeroPrefixNumber'] ?? false;
                $detailChatList     =   $mainOperation->getDetailChatListByPhoneNumber($idCountry, $phoneNumberBase);
                $idContact          =   $detailChatList['IDCONTACT'] ?? null;
                $idChatThreadType   =   1;
                
                switch($messageType){
                    case 'image'    :   $idChatThreadType   =   2; break;
                    case 'document' :   $idChatThreadType   =   3; break;
                    case 'audio'    :   $idChatThreadType   =   4; break;
                    case 'video'    :   $idChatThreadType   =   5; break;
                    case 'location' :   $idChatThreadType   =   6; break;
                    default         :   break;
                }

                $arrAdditionalThread =   [
                    'idChatThreadType'  =>  $idChatThreadType,
                    'quotedMsgId'       =>  $quotedMsgId,
                    'caption'           =>  $caption,
                    'isForwarded'       =>  $isForwarded
                ];

                if(!$fromMe){
                    if(!$detailChatList || is_null($idContact)){
                        $arrInsertContact   =   [
                            'IDCOUNTRY'             =>  $idCountry,
                            'IDNAMETITLE'           =>  0,
                            'NAMEFULL'              =>  $senderName,
                            'PHONENUMBER'           =>  $phoneNumber,
                            'PHONENUMBERBASE'       =>  $phoneNumberBase,
                            'PHONENUMBERZEROPREFIX' =>  $isZeroPrefixNumber,
                            'EMAILS'                =>  '',
                            'ISVALIDWHATSAPP'       =>  1,
                            'DATETIMEINSERT'        =>  $dateTimeNow
                        ];
                        $procInsertContact   =   $mainOperation->insertDataTable('t_contact', $arrInsertContact);
                        if($procInsertContact['status']) $idContact = $procInsertContact['insertID'];
                    }

                    if(!is_null($quotedMsgId) && $quotedMsgId != ''){
                        $detailChatThreadQuoted =   $cronModel->getDetailChatThreadQuoted($quotedMsgId);
                        $isQuotedTemplate       =   $detailChatThreadQuoted['ISTEMPLATE'];

                        if($isQuotedTemplate){
                            $detailChatCron =   $cronModel->getDetailChatCron($quotedMsgId);
                            $idReservationRC=   $detailChatCron['IDRESERVATIONRECONFIRMATION'];

                            if($idReservationRC > 0){
                                $statusReconfirmation       =   $messageBody == 'Confirm Reservation' ? 2 : 3;
                                $arrUpdateReconfirmation    =   [
                                    'DATETIMERESPONSE'  =>  $dateTimeNow,
                                    'STATUS'            =>  $statusReconfirmation
                                ];
                                $mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationreconfirmation', $arrUpdateReconfirmation, ['IDRESERVATIONRECONFIRMATION' => $idReservationRC]);
                            }
                        }
                    }

                    if(!is_null($idContact)) $mainOperation->insertUpdateChatTable($timeStamp, $idContact, $messageId, $messageBody, 0, $arrAdditionalThread);
                } else {
                    $isMessageIdExist =   $cronModel->isMessageIdExist($messageId);
                    if(!$isMessageIdExist) $mainOperation->insertUpdateChatTable($timeStamp, $idContact, $messageId, $messageBody, 1, $arrAdditionalThread);
                }

                if(!is_null($idContact)) $mainOperation->updateDataTable('t_contact', ['PHONENUMBERZEROPREFIX' => $isZeroPrefixNumber], ['IDCONTACT' => $idContact]);
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
    
    private function getDataPhoneNumberBase($phoneNumber)
    {   
        $mainOperation          =   new MainOperation();
        $dataCountryPhoneNumber =   $mainOperation->getDataCountryCodeByPhoneNumber($phoneNumber);
        $idCountry              =   $dataCountryPhoneNumber['idCountry'] ?? 0;
        $countryPhoneCode	    =   $dataCountryPhoneNumber['countryPhoneCode'] ?? '';
		$phoneNumberBase	    =	substr($phoneNumber, strlen($countryPhoneCode)) * 1;
        $isZeroPrefixNumber     =   substr($phoneNumberBase, 0, 1) == '0' ? true : false;
		
		return [
            'idCountry'         =>  $idCountry,
            'phoneNumberBase'   =>  $phoneNumberBase,
            'isZeroPrefixNumber'=>  $isZeroPrefixNumber
        ];
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
}
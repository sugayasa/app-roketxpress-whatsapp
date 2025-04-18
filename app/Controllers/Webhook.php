<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Libraries\FirebaseRTDB;
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
        $this->epochDatetime    =   Time::now()->getTimestamp();
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function whatsappOneMsgIO()
    {
        $mainOperation  =   new MainOperation();
        $firebaseRTDB   =   new FirebaseRTDB();
        $params         =   $this->request->getJSON();
        $messages       =   $params->messages ?? null;
        $acks           =   $params->ack ?? null;

        if(!is_null($messages)) {
            foreach ($messages as $message) {
                $author         =   $message->author ?? null;
                $messageId      =   $message->id ?? null;
                $messageType    =   $message->type ?? null;
                $messageBody    =   $message->body ?? null;
                $senderName     =   $message->senderName ?? null;
                $fromMe         =   $message->fromMe ?? null;
                $caption        =   $message->caption ?? null;
                $quotedMsgId    =   $message->quotedMsgId ?? null;
                $isForwarded    =   $message->isForwarded ?? null;
                $time           =   $message->time ?? null;

                if(!$fromMe){
                    $phoneNumber    =   getPhoneNumberFromWhatsappAuthor($author);
                    $idChatList     =   $mainOperation->getIdChatListByPhoneNumber($phoneNumber);
                    $time           =   Time::createFromTimestamp($time, 'GMT');
                    $dateTime       =   $time->setTimezone('Asia/Makassar')->toDateTimeString();

                    if($idChatList){
                        $arrInsertThread=   [
                            'IDCHATLIST'        =>  $idChatList,
                            'IDUSERADMIN'       =>  0,
                            'IDMESSAGE'         =>  $messageId,
                            'CHATCONTENTBODY'   =>  $messageBody,
                            'CHATDATETIME'      =>  $dateTime,
                            'STATUSREAD'        =>  0,
                            'ISTEMPLATE'        =>  false
                        ];
                        $mainOperation->insertDataTable('t_chatthread', $arrInsertThread);

                        $arrUpdateReferenceRTDB =   [
                            'idMessage' =>  $messageId,
                            'timestamp' =>  $this->epochDatetime
                        ];
                        $firebaseRTDB->updateRealtimeDatabaseValue('newMessage', $arrUpdateReferenceRTDB);
                    }
                }
            }
        } else if(!is_null($acks)) {
            foreach ($acks as $ack) {
                $idMessage      =   $ack->id ?? null;
                $ackStatus      =   $ack->status ?? null;
                $fieldDateTime  =   "DATETIMESENT";

                switch ($ackStatus) {
                    case 'sent'     :   $fieldDateTime    =   'DATETIMESENT'; break;
                    case 'delivered':   $fieldDateTime    =   'DATETIMEDELIVERED'; break;
                    case 'read'     :   $fieldDateTime    =   'DATETIMEREAD'; break;
                    default         :   $fieldDateTime    =   'DATETIMESENT'; break;
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
}
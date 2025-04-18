<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Libraries\OneMsgIO;
use App\Models\MainOperation;
use App\Models\ChatModel;

class Chat extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function getDataChatList()
    {
        $chatModel      =   new ChatModel();
        $page           =   $this->request->getVar('page');
        $searchKeyword  =   $this->request->getVar('searchKeyword');
        $idContact      =   $this->request->getVar('idContact');
        $idContact      =   isset($idContact) && !is_null($idContact) && $idContact != '' ? hashidDecode($idContact) : null;
        $dataPerPage    =   50;
        $dataChatList   =   $chatModel->getDataChatList($page, $dataPerPage, $searchKeyword, $idContact);
        $totalData      =   0;

        if($dataChatList && count($dataChatList) > 0) {
            $dataChatList   =   encodeDatabaseObjectResultKey($dataChatList, 'IDCHATLIST');

            foreach($dataChatList as $keyChatList){
                $lastMessage            =   $keyChatList->LASTMESSAGE;
                $lastMessage            =   strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30)."..." : $lastMessage;
                $lastMessageDateTime    =   $keyChatList->LASTMESSAGEDATETIME;

                $keyChatList->LASTMESSAGEDATETIME   =   getDateTimeIntervalStringInfo($lastMessageDateTime, 1);
                $keyChatList->LASTMESSAGE           =   $lastMessage;
                $totalData++;
            }
        }

        $loadMoreData   =   $totalData == $dataPerPage ? true : false;
        return $this->setResponseFormat('json')
                    ->respond([
                        "dataChatList"  =>  $dataChatList,
                        "loadMoreData"  =>  $loadMoreData
                     ]);
    }
    
    public function getDetailChat()
    {
        helper(['form']);
        $rules      =   [
            'idChatList'    =>  ['label' => 'Id contact', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idChatList'    => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $chatModel      =   new ChatModel();
        $idChatList     =   $this->request->getVar('idChatList');
        $idChatList     =   hashidDecode($idChatList);
        $page           =   $this->request->getVar('page');
        $detailContact  =   $chatModel->getDetailContactChat($idChatList);
        $listChatThread =   $chatModel->getListChatThread($idChatList, $page);
        $dateNow        =   new Time('now');
        $dateToday      =   $dateNow->format('Y-m-d');
        $dateYesterday  =   $dateNow->modify('-1 day')->format('Y-m-d');

        if($listChatThread){
            foreach($listChatThread as $keyChatThread){
                $chatDateTime   =   $keyChatThread->CHATDATETIME;
                $chatDateTimeTF =   Time::createFromFormat('Y-m-d H:i:s', $chatDateTime);
                $chatDate       =   $chatDateTimeTF->toDateString();

                if($chatDate == $dateToday){
                    $keyChatThread->DAYTITLE    =   'Today';
                } else if($chatDate == $dateYesterday) {
                    $keyChatThread->DAYTITLE    =   'Yesterday';
                } else {
                    $keyChatThread->DAYTITLE    =   $chatDateTimeTF->toLocalizedString('d MMM Y');
                }
            }
        }

        $idContact                  =   $detailContact['IDCONTACT'];
        $detailContact['IDCONTACT'] =   hashidEncode($idContact);
        $listActiveReservation      =   $chatModel->getListActiveReservation($idContact);
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailContact"         =>  $detailContact,
                        "listChatThread"        =>  array_reverse($listChatThread),
                        "listActiveReservation" =>  $listActiveReservation
                     ]);
    }

    public function sendMessage()
    {
        helper(['form']);
        $oneMsgIO       =   new OneMsgIO();
        $mainOperation  =   new MainOperation();
        $currentDateTime=   $this->currentDateTime;
        $rules          =   [
            'idContact'     =>  ['label' => 'Id Contact', 'rules' => 'required|alpha_numeric'],
            'phoneNumber'   =>  ['label' => 'Phone Number', 'rules' => 'required|numeric'],
            'message'       =>  ['label' => 'Message', 'rules' => 'required']
        ];

        $messages       =   [
            'idContact'     => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'phoneNumber'   => [
                'required'  => 'Phone number is required',
                'numeric'   => 'Invalid phone number. The {field} must contain only numbers'
            ],
            'message'       => [
                'required'  => 'Please insert message',
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idContact      =   $this->request->getVar('idContact');
        $idContact      =   hashidDecode($idContact);
        $phoneNumber    =   $this->request->getVar('phoneNumber');
        $phoneNumber    =   preg_replace('/[^0-9]/', '', $phoneNumber);
        $message        =   $this->request->getVar('message');
        $sendResult     =   $oneMsgIO->sendMessage($phoneNumber, $message);

       if(!$sendResult['isSent']){
            $errorCode  =   $sendResult['errorCode'];
            $errorMsg   =   $sendResult['errorMsg'];
            $mainOperation->insertLogFailedMessage(0, $idContact, $phoneNumber, [], $errorCode, $errorMsg);
            switch($errorCode){
                case 'E0001'    :   $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => -1], ['IDCONTACT' => $idContact]);
                                    return throwResponseInternalServerError('Message delivery failed. The recipient`s number (+'.$phoneNumber.') is not registered as a valid WhatsApp user.', $sendResult);
                case 'E1012'    :   return throwResponseInternalServerError('Invalid message sent. Please remove tab, new line and more than 4 consecutive spaces in the message', $sendResult);
                default         :   return throwResponseInternalServerError('Failed to send message. Please try again later', $sendResult);
            }
        } else {
            $idMessage  =   $sendResult['idMessage'];
            $mainOperation->insertUpdateChatTable($currentDateTime, $idContact, $idMessage, $message);

            return $this->setResponseFormat('json')
                    ->respond([
                        "idMessage"         =>  $idMessage,
                        "chatTime"          =>  Time::now()->toLocalizedString('H:mm'),
                        "initialName"       =>  $this->userData->initialName[0],
                        "userNameChat"      =>  $this->userData->name,
                        "arrayChatThread"   =>  [
                            "IDMESSAGE"         =>  $idMessage,
                            "CHATCONTENTHEADER" =>  "",
                            "CHATCONTENTBODY"   =>  $message,
                            "CHATCONTENTFOOTER" =>  "",
                            "DATETIMESENT"      =>  null,
                            "DATETIMEDELIVERED" =>  null,
                            "DATETIMEREAD"      =>  null
                        ]
                     ]);
        }
    }
}
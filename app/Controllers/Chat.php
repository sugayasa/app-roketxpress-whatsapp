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
    protected $userData, $currentDateTime, $currentTimeStamp;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
            $this->currentTimeStamp =   $request->currentTimeStamp;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function getDataChatList()
    {
        $chatModel          =   new ChatModel();
        $page               =   $this->request->getVar('page');
        $searchKeyword      =   $this->request->getVar('searchKeyword');
        $chatType           =   $this->request->getVar('chatType');
        $idContact          =   $this->request->getVar('idContact');
        $idContact          =   isset($idContact) && !is_null($idContact) && $idContact != '' ? hashidDecode($idContact) : null;
        $dataPerPage        =   50;
        $dataChatList       =   $chatModel->getDataChatList($page, $dataPerPage, $searchKeyword, $chatType, $idContact);
        $totalData          =   0;

        if($dataChatList && count($dataChatList) > 0) {
            $dataChatList       =   encodeDatabaseObjectResultKey($dataChatList, 'IDCHATLIST', true);
            $userTimeZoneOffset =   $this->userData->userTimeZoneOffset;

            foreach($dataChatList as $keyChatList){
                $lastMessage    =   $keyChatList->LASTMESSAGE;

                if(substr($lastMessage, 0, 2)  != '<i'){
                    $lastMessage    =   strlen($lastMessage) > 30 ? substr($lastMessage, 0, 30)."..." : $lastMessage;
                    $lastMessage    =   mb_convert_encoding($lastMessage, 'UTF-8', 'UTF-8');
                }

                $lastMessageDateTime    =   $keyChatList->DATETIMELASTMESSAGE;
                $lastMessageDateTimeTF  =   Time::createFromTimestamp($lastMessageDateTime, 'UTC')->setTimezone($userTimeZoneOffset);
                $lastMessageDateTimeStr =   $lastMessageDateTimeTF->toLocalizedString('yyyy-MM-dd HH:mm:ss');

                $keyChatList->DATETIMELASTMESSAGESTR=   getDateTimeIntervalStringInfo($lastMessageDateTimeStr, 1);
                $keyChatList->LASTMESSAGE           =   $lastMessage;
                $totalData++;
            }

            $loadMoreData   =   $totalData == $dataPerPage ? true : false;
            return $this->setResponseFormat('json')
                        ->respond([
                            "dataChatList"  =>  $dataChatList,
                            "loadMoreData"  =>  $loadMoreData
                        ]);
                        error_log(json_last_error_msg());
        } else {
            return throwResponseNotFound('No conversation found');
        }
    }
    
    public function getDetailChat()
    {
        helper(['form']);
        $rules          =   [
            'idChatList'    =>  ['label' => 'Id contact', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idChatList'    => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $mainOperation      =   new MainOperation();
        $chatModel          =   new ChatModel();
        $idChatList         =   $this->request->getVar('idChatList');
        $idChatList         =   hashidDecode($idChatList, true);
        $page               =   $this->request->getVar('page');
        $idUserAdmin        =   $this->userData->idUserAdmin;
        $userTimeZoneOffset =   $this->userData->userTimeZoneOffset;
        $detailContact      =   $chatModel->getDetailContactChat($idChatList);
        $listChatThread     =   $chatModel->getListChatThread($idChatList, $page);
        $dateNow            =   new Time('now');
        $dateToday          =   $dateNow->format('Y-m-d');
        $dateYesterday      =   $dateNow->modify('-1 day')->format('Y-m-d');

        if($listChatThread){
            foreach($listChatThread as $keyChatThread){
                $dateTimeChat   =   $keyChatThread->DATETIMECHAT;
                $dateTimeChatTF =   Time::createFromTimestamp($dateTimeChat, 'UTC')->setTimezone($userTimeZoneOffset);
                $chatDate       =   $dateTimeChatTF->toDateString();

                $keyChatThread->CHATTIME    =   $dateTimeChatTF->toLocalizedString('H:mm');
                if($chatDate == $dateToday){
                    $keyChatThread->DAYTITLE    =   'Today';
                } else if($chatDate == $dateYesterday) {
                    $keyChatThread->DAYTITLE    =   'Yesterday';
                } else {
                    $keyChatThread->DAYTITLE    =   $dateTimeChatTF->toLocalizedString('d MMM Y');
                }

                $idChatThread       =   $keyChatThread->IDCHATTHREAD;
                $arrIdUserAdminRead =   $keyChatThread->ARRIDUSERADMINREAD;
                $arrIdUserAdminRead =   json_decode($arrIdUserAdminRead, true);
                $isIdUserAdminExists=   in_array($idUserAdmin, $arrIdUserAdminRead);

                if(!$isIdUserAdminExists){
                    $arrInsertChatDetailRead    =   [
                        'IDUSERADMIN'     =>  $idUserAdmin,
                        'IDCHATTHREAD'    =>  $idChatThread,
                        'DATETIMEREAD'    =>  $this->currentTimeStamp
                    ];
                    $mainOperation->insertDataTable('t_chatdetailread', $arrInsertChatDetailRead);
                }

                $mainOperation->updateDataTable('t_chatthread', ['STATUSREAD' => 1], ['IDCHATTHREAD' => $idChatThread]);
                unset($keyChatThread->ARRIDUSERADMINREAD);
            }
            $listChatThread =   encodeDatabaseObjectResultKey($listChatThread, 'IDCHATTHREAD', true);
            $mainOperation->updateChatListAndRTDBStats($idChatList, false);
        }

        $idContact                  =   $detailContact['IDCONTACT'];
        $detailContact['IDCONTACT'] =   hashidEncode($idContact);
        $listActiveReservation      =   $chatModel->getListActiveReservation($idContact);
        $listActiveReservation      =   encodeDatabaseObjectResultKey($listActiveReservation, 'IDRESERVATION');
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailContact"         =>  $detailContact,
                        "listChatThread"        =>  array_reverse($listChatThread),
                        "listActiveReservation" =>  $listActiveReservation
                     ]);
    }
    
    public function getMoreChatThread()
    {
        helper(['form']);
        $rules          =   [
            'idChatList'=>  ['label' => 'Id contact', 'rules' => 'required|alpha_numeric'],
            'page'      =>  ['label' => 'Id contact', 'rules' => 'required|numeric']
        ];

        $messages   =   [
            'idChatList'=> [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ],
            'page'      => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $chatModel          =   new ChatModel();
        $idChatList         =   $this->request->getVar('idChatList');
        $idChatList         =   hashidDecode($idChatList, true);
        $page               =   $this->request->getVar('page');
        $listChatThread     =   $chatModel->getListChatThread($idChatList, $page);

        if(!$listChatThread){
            return throwResponseNotFound('No more conversation found');
        } else {
            $userTimeZoneOffset =   $this->userData->userTimeZoneOffset;
            $dateNow            =   new Time('now');
            $dateToday          =   $dateNow->format('Y-m-d');
            $dateYesterday      =   $dateNow->modify('-1 day')->format('Y-m-d');
    
            foreach($listChatThread as $keyChatThread){
                $dateTimeChat   =   $keyChatThread->DATETIMECHAT;
                $dateTimeChatTF =   Time::createFromTimestamp($dateTimeChat, 'UTC')->setTimezone($userTimeZoneOffset);
                $chatDate       =   $dateTimeChatTF->toDateString();

                $keyChatThread->CHATTIME    =   $dateTimeChatTF->toLocalizedString('H:mm');
                if($chatDate == $dateToday){
                    $keyChatThread->DAYTITLE    =   'Today';
                } else if($chatDate == $dateYesterday) {
                    $keyChatThread->DAYTITLE    =   'Yesterday';
                } else {
                    $keyChatThread->DAYTITLE    =   $dateTimeChatTF->toLocalizedString('d MMM Y');
                }

            }

            $listChatThread =   encodeDatabaseObjectResultKey($listChatThread, 'IDCHATTHREAD', true);
            return $this->setResponseFormat('json')
                        ->respond([
                            "listChatThread"    =>  array_reverse($listChatThread),
                         ]);
        }
    }

    public function getDetailThreadACK()
    {
        helper(['form']);
        $rules      =   [
            'idChatThread'    =>  ['label' => 'Id Chat Thread', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idChatThread'    => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $chatModel      =   new ChatModel();
        $idChatThread   =   $this->request->getVar('idChatThread');
        $idChatThread   =   hashidDecode($idChatThread, true);
        $dataThreadACK  =   $chatModel->getDataThreadACK($idChatThread);

        if(!$dataThreadACK) return throwResponseNotFound('Details not found');
        return $this->setResponseFormat('json')
                    ->respond([
                        "dataThreadACK" =>  $dataThreadACK
                     ]);
    }

    public function sendMessage()
    {
        helper(['form']);
        $oneMsgIO           =   new OneMsgIO();
        $mainOperation      =   new MainOperation();
        $currentTimeStamp   =   $this->currentTimeStamp;
        $rules              =   [
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

        $idContact          =   $this->request->getVar('idContact');
        $idContact          =   hashidDecode($idContact);
        $phoneNumber        =   $this->request->getVar('phoneNumber');
        $phoneNumber        =   preg_replace('/[^0-9]/', '', $phoneNumber);
        $activePhoneNumber  =   $mainOperation->getActivePhoneNumber($idContact) ?? $phoneNumber;
        $message            =   $this->request->getVar('message');
        $sendResult         =   $oneMsgIO->sendMessage($activePhoneNumber, $message);

       if(!$sendResult['isSent']){
            $errorCode  =   $sendResult['errorCode'];
            $errorMsg   =   $sendResult['errorMsg'];
            $mainOperation->insertLogFailedMessage(0, $idContact, $activePhoneNumber, [], $errorCode, $errorMsg);
            switch($errorCode){
                case 'E0001'    :   $mainOperation->updateDataTable('t_contact', ['ISVALIDWHATSAPP' => -1], ['IDCONTACT' => $idContact]);
                                    return throwResponseInternalServerError('Message delivery failed. The recipient`s number (+'.$activePhoneNumber.') is not registered as a valid WhatsApp user.', $sendResult);
                case 'E1012'    :   return throwResponseInternalServerError('Invalid message sent. Please remove tab, new line and more than 4 consecutive spaces in the message', $sendResult);
                default         :   return throwResponseInternalServerError('Failed to send message. Please try again later', $sendResult);
            }
        } else {
            $idMessage  =   $sendResult['idMessage'];
            $idUserAdmin=   $this->userData->idUserAdmin;
            $mainOperation->insertUpdateChatTable($currentTimeStamp, $idContact, $idMessage, $message, $idUserAdmin);

            return throwResponseOK('Message sent successfully', [
                'currentTimeStamp'  =>  $currentTimeStamp
            ]);
        }
    }

    public function updateUnreadMessageCount()
    {
        $mainOperation          =   new MainOperation();
        $chatModel              =   new ChatModel();
        $idChatList             =   $this->request->getVar('idChatList');
        $idChatList             =   hashidDecode($idChatList, true);
        $idUserAdmin            =   $this->userData->idUserAdmin;
        $dataUnreadChatThread   =   $chatModel->getDataUnreadChatThread($idChatList);

        if($dataUnreadChatThread){
            foreach($dataUnreadChatThread as $keyUnreadChatThread){
                $idChatThread           =   $keyUnreadChatThread->IDCHATTHREAD;
                $arrInsertChatDetailRead=   [
                    'IDUSERADMIN'     =>  $idUserAdmin,
                    'IDCHATTHREAD'    =>  $idChatThread,
                    'DATETIMEREAD'    =>  $this->currentTimeStamp
                ];

                $mainOperation->insertDataTable('t_chatdetailread', $arrInsertChatDetailRead);
                $mainOperation->updateDataTable('t_chatthread', ['STATUSREAD' => 1], ['IDCHATTHREAD' => $idChatThread]);
            }
        }

        $mainOperation->updateChatListAndRTDBStats($idChatList, false);
        return throwResponseOK('Unread message count updated successfully');
    }
    
    public function getDetailReservation()
    {
        helper(['form']);
        $rules          =   [
            'idReservation' =>  ['label' => 'Id Reservation', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idReservation' => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $chatModel          =   new ChatModel();
        $idReservation      =   $this->request->getVar('idReservation');
        $idReservation      =   hashidDecode($idReservation);
        $detailReservation  =   $chatModel->getDetailReservation($idReservation);

        if(!$detailReservation) return throwResponseNotFound('Reservation details not found');
        $dataCurrencyExchange           =   $chatModel->getDataCurrencyExchange();
        $detailReservation['IDAREA']    =   hashidEncode($detailReservation['IDAREA']);
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailReservation"     =>  $detailReservation,
                        "dataCurrencyExchange"  =>  $dataCurrencyExchange
                     ]);
    }

    public function saveReservation()
    {
        helper(['form']);
        $rules              =   [
            'modalEditReservation-title'                    =>  ['label' => 'Title', 'rules' => 'required'],
            'modalEditReservation-durationDay'              =>  ['label' => 'Duration Day', 'rules' => 'required|numeric|greater_than[0]|less_than[100]'],
            'modalEditReservation-date'                     =>  ['label' => 'Reservation Date', 'rules' => 'required|exact_length[10]|valid_date[d-m-Y]'],
            'modalEditReservation-timeHour'                 =>  ['label' => 'Reservation Hour', 'rules' => 'required|regex_match[/^(0[0-9]|1[0-9]|2[0-3])$/]'],
            'modalEditReservation-timeMinute'               =>  ['label' => 'Reservation Minute', 'rules' => 'required|numeric|regex_match[/^([0-5][0-9])$/]'],
            'modalEditReservation-pickUpArea'               =>  ['label' => 'Pick up Area', 'rules' => 'required|regex_match[/^(-1|[a-zA-Z0-9]+)$/]'],
            'modalEditReservation-paxAdult'                 =>  ['label' => 'Pax (Adult)', 'rules' => 'required|numeric|greater_than[0]|less_than[100]'],
            'modalEditReservation-incomeCurrency'           =>  ['label' => 'Currency', 'rules' => 'required|regex_match[/^[A-Z]{3}$/]'],
            'modalEditReservation-incomeInteger'            =>  ['label' => 'Income Integer', 'rules' => 'required|regex_match[/^(1|[1-9]\d{0,2}(,\d{3})*)$/]'],
            'modalEditReservation-incomeComma'              =>  ['label' => 'Income Comma', 'rules' => 'required|regex_match[/^(0[0-9]|[1-9][0-9])$/]'],
            'modalEditReservation-incomeCurrencyExchange'   =>  ['label' => 'Currency Exchange', 'rules' => 'required|regex_match[/^(1|[1-9]\d{0,2}(,\d{3})*)$/]'],
            'modalEditReservation-idReservation'            =>  ['label' => 'Id Reservation', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'modalEditReservation-timeHour' =>  [
                'regex_match'   =>  '{field} must be between 00 and 23'
            ],
            'modalEditReservation-timeMinute'   =>  [
                'regex_match'   =>  '{field} must be between 00 and 59'
            ],
            'modalEditReservation-pickUpArea'   =>  [
                'regex_match'   =>  'Invalid selected {field}'
            ],
            'modalEditReservation-incomeCurrency'   =>  [
                'regex_match'   =>  'Invalid selected {field}'
            ],
            'modalEditReservation-incomeInteger'    =>  [
                'regex_match'   =>  'The {field} must contain only numbers and comma (,) as thousand separator'
            ],
            'modalEditReservation-incomeComma'      =>  [
                'regex_match'   =>  '{field} must be between 00 and 99'
            ],
            'modalEditReservation-incomeCurrencyExchange'   =>  [
                'regex_match'   =>  'The {field} must contain only numbers and comma (,) as thousand separator'
            ],
            'modalEditReservation-idReservation'    => [
                'required'      => 'Invalid data sent',
                'alpha_numeric' => 'Invalid data sent'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $reservationTitle       =   $this->request->getVar('modalEditReservation-title');
        $durationDay            =   $this->request->getVar('modalEditReservation-durationDay');
        $reservationDate        =   $this->request->getVar('modalEditReservation-date');
        $reservationDate        =   Time::createFromFormat('d-m-Y', $reservationDate)->format('Y-m-d');
        $reservationTimeHour    =   $this->request->getVar('modalEditReservation-timeHour');
        $reservationTimeMinute  =   $this->request->getVar('modalEditReservation-timeMinute');
        $reservationTime        =   $reservationTimeHour.":".$reservationTimeMinute.":00";
        $idPickUpArea           =   $this->request->getVar('modalEditReservation-pickUpArea');
        $idPickUpArea           =   $idPickUpArea != -1 ? hashidDecode($idPickUpArea) : $idPickUpArea;
        $hotelName              =   $this->request->getVar('modalEditReservation-hotelName');
        $pickupLocation         =   $this->request->getVar('modalEditReservation-pickupLocation');
        $pickupLocationLinkUrl  =   $this->request->getVar('modalEditReservation-pickupLocationLinkUrl');
        $dropOffLocation        =   $this->request->getVar('modalEditReservation-dropOffLocation');
        $paxAdult               =   $this->request->getVar('modalEditReservation-paxAdult');
        $paxChild               =   $this->request->getVar('modalEditReservation-paxChild');
        $paxInfant              =   $this->request->getVar('modalEditReservation-paxInfant');
		$paxTotal				=	$paxAdult + $paxChild + $paxChild;
        $incomeCurrency         =   $this->request->getVar('modalEditReservation-incomeCurrency');
        $incomeInteger          =   $this->request->getVar('modalEditReservation-incomeInteger');
        $incomeInteger          =   str_replace(',', '', $incomeInteger);
        $incomeComma            =   $this->request->getVar('modalEditReservation-incomeComma');
        $incomeTotal            =   ($incomeInteger.".".$incomeComma) * 1;
        $tourPlan	            =   $this->request->getVar('modalEditReservation-tourPlan');
        $remark                 =   $this->request->getVar('modalEditReservation-remark');
        $specialRequest         =   $this->request->getVar('modalEditReservation-specialRequest');
        $idReservation          =   $this->request->getVar('modalEditReservation-idReservation');
        $idReservation          =   hashidDecode($idReservation);

        if($idPickUpArea == -1){
			if($hotelName != "" || $pickupLocation != "" || $dropOffLocation != ""){
                return throwResponseNotAcceptable('Please select a valid area!<br/><br/> <b>Without Transfer</b> can only be selected if the <b>hotel, pick up and drop off location</b> are blank');
			}
		}
		
		if($idPickUpArea != -1){
			if($hotelName == "" && $pickupLocation == "" && $dropOffLocation == ""){
                return throwResponseNotAcceptable('Please enter one of the <b>hotel name, pick up or drop off location</b>');
			}
		}

        $mainOperation      =   new MainOperation();
        $currencyExchange	=	$incomeCurrency == "IDR" ? 1 : $mainOperation->getCurrencyExchangeByDate($incomeCurrency, $reservationDate);
		$incomeTotalIDR     =	$incomeTotal * $currencyExchange;
		$userAdminName		=	$this->userData->name;
        
		if(strpos(strtolower($reservationTitle), "japan") !== false && strpos(strtolower($specialRequest), "japan") === false) $specialRequest	=	"Japanese Driver. ".$specialRequest;
		if(strpos(strtolower($reservationTitle), "chinese") !== false && strpos(strtolower($specialRequest), "chinese") === false) $specialRequest	=	"Chinese Driver. ".$specialRequest;

        $arrUpdateRsv		=	[
			"IDAREA"				=>	$idPickUpArea,
			"RESERVATIONTITLE"		=>	$reservationTitle,
			"DURATIONOFDAY"			=>	$durationDay,
			"RESERVATIONDATESTART"	=>	$reservationDate,
			"RESERVATIONDATEEND"	=>	$reservationDate,
			"RESERVATIONTIMESTART"	=>	$reservationTime,
			"RESERVATIONTIMEEND"	=>	$reservationTime,
			"HOTELNAME"				=>	$hotelName,
			"PICKUPLOCATION"		=>	$pickupLocation,
			"DROPOFFLOCATION"		=>	$dropOffLocation,
			"NUMBEROFADULT"			=>	$paxAdult,
			"NUMBEROFCHILD"			=>	$paxChild,
			"NUMBEROFINFANT"		=>	$paxInfant,
			"INCOMEAMOUNTCURRENCY"	=>	$incomeCurrency,
			"INCOMEAMOUNT"			=>	$incomeTotal,
			"INCOMEEXCHANGECURRENCY"=>	$currencyExchange,
			"INCOMEAMOUNTIDR"		=>	$incomeTotalIDR,
			"REMARK"				=>	$remark,
			"TOURPLAN"				=>	$tourPlan,
			"SPECIALREQUEST"		=>	$specialRequest,
			"URLPICKUPLOCATION"		=>	$pickupLocationLinkUrl,
			"USERLASTUPDATE"		=>	$userAdminName,
			"DATETIMELASTUPDATE"	=>	$this->currentDateTime
        ];

        $reservationDateEnd =	$reservationDate;
		if($durationDay > 1){
			$additionalDays						=	$durationDay - 1;
			$reservationDateEnd                 =	date('Y-m-d', strtotime($reservationDate. ' + '.$additionalDays.' days'));
			$arrUpdateRsv['RESERVATIONDATEEND']	=	$reservationDateEnd;
		}

		$detailReservation      =	$mainOperation->getDetailReservation($idReservation);
		$upsellingType			=	$detailReservation['UPSELLINGTYPE'];
		$dateStartReservation	=	$detailReservation['RESERVATIONDATESTART'];
		$dateEndReservation		=	$detailReservation['RESERVATIONDATEEND'];
		$totalDetails			=	$detailReservation['TOTALDETAILS'];
		$oldIdArea				=	$detailReservation['IDAREA'];

        if(($dateStartReservation != $reservationDate || $dateEndReservation != $reservationDateEnd) && $totalDetails > 0){
			return throwResponseNotAcceptable("Please remove all reservation/cost details before changing reservation date");
		}
		
		if($oldIdArea != $idPickUpArea && $oldIdArea != 0 && $totalDetails > 0){
            return throwResponseNotAcceptable("Please remove all reservation/cost details before changing <b>pick up area</b>");
		}
		
		$mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationdetails', ["SCHEDULETYPE"=>1], ["IDRESERVATION" => $idReservation, "IDPRODUCTTYPE"=>2]);		
		if($totalDetails > 0){
			if((isset($specialRequest) && $specialRequest != "" && $specialRequest != "-") || $durationDay > 1 || $paxTotal > 6 || $upsellingType == 1){
				$mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservationdetails', ["SCHEDULETYPE"=>2], ["IDRESERVATION" => $idReservation, "IDPRODUCTTYPE"=>2]);
			}
		}

		$procUpdateRsv	=	$mainOperation->updateDataTable(APP_MAIN_DATABASE_NAME.'.t_reservation', $arrUpdateRsv, ["IDRESERVATION" => $idReservation]);
		
		if(!$procUpdateRsv['status']) return switchMySQLErrorCode($procUpdateRsv['errCode']);
        return throwResponseOK(
            'Reservation data updated successfully',
            [
                'dataUpdate'    =>  [
                    'reservationTitle'  =>  $reservationTitle,
                    'reservationDateStr'=>  Time::createFromFormat('Y-m-d', $reservationDate)->format('D, d M Y'),
                    'reservationTimeStr'=>  $reservationTimeHour.":".$reservationTimeMinute,
                    'paxDetailStr'      =>  $paxAdult." Adult, ".$paxChild." Child, ".$paxInfant." Infant",
                    'hotelName'         =>  $hotelName == "" ? '-' : $hotelName,
                    'pickupLocation'    =>  $pickupLocation == "" ? '-' : $pickupLocation,
                    'dropOffLocation'   =>  $dropOffLocation == "" ? '-' : $dropOffLocation,
                    'tourPlan'          =>  $tourPlan == "" ? '-' : $tourPlan,
                    'remark'            =>  $remark == "" ? '-' : $remark,
                    'specialRequest'    =>  $specialRequest == "" ? '-' : $specialRequest
                ]
            ]
        );
    }
}